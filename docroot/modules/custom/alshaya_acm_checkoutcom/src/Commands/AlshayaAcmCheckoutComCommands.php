<?php

namespace Drupal\alshaya_acm_checkoutcom\Commands;

use Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Provides commands for checkout.com upapi.
 *
 * @package Drupal\alshaya_acm_checkoutcom\Commands
 */
class AlshayaAcmCheckoutComCommands extends DrushCommands {

  /**
   * The api helper object.
   *
   * @var \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper
   */
  protected $apiHelper;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * AlshayaAcmCheckoutComCommands constructor.
   *
   * @param \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper $api_helper
   *   Api helper object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   */
  public function __construct(
    AlshayaAcmCheckoutComAPIHelper $api_helper,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->apiHelper = $api_helper;
    $this->logger = $logger_factory->get('alshaya_acm_checkoutcom');
  }

  /**
   * Syncs and resets checkout.com upapi config cache.
   *
   * @command alshaya_acm_checkoutcom:sync-config
   *
   * @aliases sync-checkoutupapi-com-config
   */
  public function syncCheckoutComConfig() {
    // Reset magento checkout.com config cache.
    $this->apiHelper->getCheckoutcomUpApiConfig(TRUE);
    $this->logger->notice('checkout.com upapi config info cache reset.');
  }

}
