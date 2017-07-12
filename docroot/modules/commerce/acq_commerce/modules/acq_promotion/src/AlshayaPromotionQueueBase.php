<?php

namespace Drupal\acq_promotion;

use Drupal\acq_commerce\Conductor\IngestRequestTrait;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaPromotionQueueBase.
 *
 * @package Drupal\acq_promotion
 */
abstract class AlshayaPromotionQueueBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  use IngestRequestTrait;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerInterface
   */
  protected $logger;

  /**
   * Api version.
   *
   * @var string
   */
  protected $apiVersion;

  /**
   * AcqPromotionAttachQueue constructor.
   *
   * @param array $configuration
   *   Plugin config.
   * @param string $plugin_id
   *   Plugin unique id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config Factory service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              LoggerInterface $logger,
                              ConfigFactory $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
    $this->apiVersion = $configFactory->get('acq_commerce.conductor')->get('api_version');
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
      $container->get('logger.factory')->get('acq_commerce'),
      $container->get('config.factory')
    );
  }

}
