<?php

namespace Drupal\acq_promotion;

use Drupal\acq_commerce\Conductor\IngestAPIWrapper;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaPromotionQueueBase.
 *
 * @package Drupal\acq_promotion
 */
abstract class AcqPromotionQueueBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * IngestAPIWrapper Service object.
   *
   * @var \Drupal\acq_commerce\Conductor\IngestAPIWrapper
   */
  protected $ingestApiWrapper;

  /**
   * LoggerChannelInterface object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * AcqPromotionAttachQueue constructor.
   *
   * @param array $configuration
   *   Plugin config.
   * @param string $plugin_id
   *   Plugin unique id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\acq_commerce\Conductor\IngestAPIWrapper $ingestApiWrapper
   *   IngestAPIWrapper Service object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerFactory
   *   Logger service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              IngestAPIWrapper $ingestApiWrapper,
                              LoggerChannelFactory $loggerFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->ingestApiWrapper = $ingestApiWrapper;
    $this->logger = $loggerFactory->get('acq_sku');
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('acq_commerce.ingest_api'),
      $container->get('logger.factory')
    );
  }

}
