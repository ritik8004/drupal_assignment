<?php

namespace Drupal\alshaya_acm_promotion\Commands;

use Drupal\alshaya_acm_promotion\AlshayaAcmPromotionAPIHelper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Provides commands for promotion config.
 *
 * @package Drupal\alshaya_acm_promotion\Commands
 */
class PromotionConfigCommands extends DrushCommands {

  /**
   * The api helper object.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaAcmPromotionAPIHelper
   */
  protected $apiHelper;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $drupalLogger;

  /**
   * PromotionConfigCommands constructor.
   *
   * @param \Drupal\alshaya_acm_promotion\AlshayaAcmPromotionAPIHelper $api_helper
   *   Api helper object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   */
  public function __construct(
    AlshayaAcmPromotionAPIHelper $api_helper,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->apiHelper = $api_helper;
    $this->logger = $logger_factory->get('alshaya_acm_promotion');
  }

  /**
   * Syncs and resets promo config cache.
   *
   * @command alshaya_acm_promotion:sync-promotion-config
   *
   * @aliases sync-promotion-config
   */
  public function syncPromotionConfig() {
    // Reset magento promotion config cache.
    $this->apiHelper->getDiscountTextVisibilityStatus(TRUE);
    $this->logger->notice('Promotion config info cache reset.');
  }

}
