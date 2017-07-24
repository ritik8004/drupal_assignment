<?php

namespace Drupal\acq_promotion;

use Drupal\acq_commerce\Conductor\ClientFactory;
use Drupal\acq_commerce\Conductor\IngestRequestTrait;
use Drupal\Core\Config\ConfigFactory;
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
  use IngestRequestTrait;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * Api version.
   *
   * @var string
   */
  protected $apiVersion;

  /**
   * Debug directory for Ingest API call.
   *
   * @var array|mixed|null
   */
  protected $debugDir;

  /**
   * AcqPromotionAttachQueue constructor.
   *
   * @param array $configuration
   *   Plugin config.
   * @param string $plugin_id
   *   Plugin unique id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerFactory
   *   Logger service.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config Factory service.
   * @param \Drupal\acq_commerce\Conductor\ClientFactory $clientFactory
   *   Client Factory service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              LoggerChannelFactory $loggerFactory,
                              ConfigFactory $configFactory,
                              ClientFactory $clientFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->loggerFactory = $loggerFactory;
    $this->apiVersion = $configFactory->get('acq_commerce.conductor')->get('api_version');
    $this->clientFactory = $clientFactory;
    $this->logger = $loggerFactory->get('acq_sku');
    $this->debug = $configFactory->get('acq_commerce.conductor')->get('debug');
    $this->debugDir = $configFactory->get('acq_commerce.conductor')->get('debug_dir');
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
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('acq_commerce.client_factory')
    );
  }

}
