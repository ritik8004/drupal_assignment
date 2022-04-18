<?php

namespace Drupal\alshaya_acm_promotion\Commands;

use Drupal\alshaya_acm_promotion\AlshayaAcmPromoLabelAPIHelper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Provides commands for promo label config.
 *
 * @package Drupal\alshaya_acm_promotion\Commands
 */
class PromotionLabelConfigCommands extends DrushCommands {

  /**
   * The api helper object.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaAcmPromoLabelAPIHelper
   */
  protected $apiHelper;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * PromotionLabelConfigCommands constructor.
   *
   * @param \Drupal\alshaya_acm_promotion\AlshayaAcmPromoLabelAPIHelper $api_helper
   *   Api helper object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   */
  public function __construct(
    AlshayaAcmPromoLabelAPIHelper $api_helper,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->apiHelper = $api_helper;
    $this->logger = $logger_factory->get('alshaya_acm_promotion');
  }

  /**
   * Syncs and resets promo label config cache.
   *
   * @command alshaya_acm_promotion:sync-promo-lable-config
   *
   * @aliases sync-promo-lable-config
   */
  public function syncPromoLabelConfig() {
    // Reset magento promo label config cache.
    $this->apiHelper->getPromoLabelApiConfig(TRUE);
    $this->logger->notice('Promo label config info cache reset.');
  }

}
