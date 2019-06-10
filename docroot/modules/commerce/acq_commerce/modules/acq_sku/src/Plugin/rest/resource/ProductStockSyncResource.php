<?php

namespace Drupal\acq_sku\Plugin\rest\resource;

use Drupal\acq_sku\StockManager;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ProductStockSyncResource.
 *
 * @package Drupal\acq_sku\Plugin
 *
 * @ingroup acq_sku
 *
 * @RestResource(
 *   id = "acq_productstocksync",
 *   label = @Translation("Acquia Commerce Product Stock Sync"),
 *   uri_paths = {
 *     "canonical" = "/productstocksync",
 *     "https://www.drupal.org/link-relations/create" = "/productstocksync"
 *   }
 * )
 */
class ProductStockSyncResource extends ResourceBase {

  /**
   * Stock Manager.
   *
   * @var \Drupal\acq_sku\StockManager
   */
  private $stockManager;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\acq_sku\StockManager $stock_manager
   *   Stock Manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              StockManager $stock_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->stockManager = $stock_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get(self::class),
      $container->get('acq_sku.stock_manager')
    );
  }

  /**
   * Post.
   *
   * Handle Conductor posting an array of product / SKU data for update.
   *
   * @param array $message
   *   Stock Data.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   HTTP Response object.
   */
  public function post(array $message) {
    $this->logger->debug('Stock message received. @message', [
      '@message' => json_encode($message),
    ]);

    // Work with single message and array of messages.
    $stockArray = array_key_exists('sku', $message) ? [$message] : $message;

    foreach ($stockArray as $stock) {
      try {
        $this->stockManager->processStockMessage($stock);
      }
      catch (\Exception $e) {
        $this->logger->error('Failed to process stock message: @message, exception: @exception', [
          '@message' => json_encode($stock),
          '@exception' => $e->getMessage(),
        ]);
      }
      catch (\Throwable $e) {
        $this->logger->error('Failed to process stock message: @message, exception: @exception', [
          '@message' => json_encode($stock),
          '@exception' => $e->getMessage(),
        ]);
      }
    }

    // Always return success to ACM.
    // We already log invalid data or exceptions.
    $response = ['success' => TRUE];
    return (new ModifiedResourceResponse($response));
  }

}
