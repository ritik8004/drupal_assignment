<?php

namespace Drupal\acq_sku\Plugin\rest\resource;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
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
   * Drupal Config Factory Instance.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              ConfigFactoryInterface $config_factory,
                              LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->configFactory = $config_factory;
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
      $container->get('config.factory'),
      $container->get('logger.factory')->get('acq_commerce')
    );
  }

  /**
   * Post.
   *
   * Handle Conductor posting an array of product / SKU data for update.
   *
   * @param array $stock
   *   Stock Data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   HTTP Response object.
   */
  public function post(array $stock = []) {
    $lock = \Drupal::lock();

    $response = [
      'success' => FALSE,
    ];

    $config = $this->configFactory->get('acq_commerce.conductor');
    $debug = $config->get('debug');
    $debug_dir = $config->get('debug_dir');

    if ($debug && !empty($debug_dir)) {
      // Export product data into file.
      if (!isset($fps)) {
        $filename = $debug_dir . '/stock.data';
        $fps = fopen($filename, 'a');
      }
      fwrite($fps, var_export($stock, 1));
      fwrite($fps, '\n');
    }

    if (!isset($stock['sku']) || !strlen($stock['sku'])) {
      $this->logger->error('Invalid or empty product SKU.');
      return (new ResourceResponse($response));
    }

    $langcode = NULL;

    if (isset($stock['store_id'])) {
      $langcode = acq_commerce_get_langcode_from_store_id($stock['store_id']);

      if (empty($langcode)) {
        // It could be for a different store/website, don't do anything.
        return (new ResourceResponse($response));
      }
    }

    $lock_key = 'synchronizeProduct' . $stock['sku'];

    // Acquire lock to ensure parallel processes are executed one by one.
    do {
      $lock_acquired = $lock->acquire($lock_key);

      // Sleep for half a second before trying again.
      if (!$lock_acquired) {
        usleep(500000);
      }
    } while (!$lock_acquired);

    /** @var \Drupal\acq_sku\Entity\SKU $sku */
    if ($sku = SKU::loadFromSku($stock['sku'], $langcode)) {
      $this->logger->info('Updating stock for SKU @sku.', ['@sku' => $stock['sku']]);

      if (isset($stock['is_in_stock']) && empty($stock['is_in_stock'])) {
        $stock['qty'] = 0;
      }

      $quantity = isset($stock['qty']) ? $stock['qty'] : 0;

      if ($quantity != $sku->get('stock')->getString()) {
        $sku->get('stock')->setValue($quantity);
        $sku->save();

        // Clear product and forms related to sku.
        Cache::invalidateTags(['acq_sku:' . $sku->id()]);
      }
    }

    // Release the lock.
    $lock->release($lock_key);

    if (isset($fps)) {
      fclose($fps);
    }

    $response = [
      'success' => TRUE,
    ];

    return (new ResourceResponse($response));
  }

}
