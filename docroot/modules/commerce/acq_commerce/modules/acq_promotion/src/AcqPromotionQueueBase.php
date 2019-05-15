<?php

namespace Drupal\acq_promotion;

use Drupal\acq_commerce\Conductor\IngestAPIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\Core\Database\Connection;
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
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  protected $i18nHelper;

  /**
   * Promotion manager.
   *
   * @var \Drupal\acq_promotion\AcqPromotionsManager
   */
  protected $promotionManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

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
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   * @param \Drupal\acq_promotion\AcqPromotionsManager $promotion_manager
   *   Promotion manager.
   * @param \Drupal\Core\Database\Connection $db
   *   Database connection.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              IngestAPIWrapper $ingestApiWrapper,
                              LoggerChannelFactory $loggerFactory,
                              I18nHelper $i18n_helper,
                              AcqPromotionsManager $promotion_manager,
                              Connection $db) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->ingestApiWrapper = $ingestApiWrapper;
    $this->logger = $loggerFactory->get('acq_sku');
    $this->i18nHelper = $i18n_helper;
    $this->promotionManager = $promotion_manager;
    $this->db = $db;
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
      $container->get('logger.factory'),
      $container->get('acq_commerce.i18n_helper'),
      $container->get('acq_promotion.promotions_manager'),
      $container->get('database')
    );
  }

}
