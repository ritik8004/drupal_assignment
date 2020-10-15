<?php

namespace Drupal\acq_checkoutcom\Commands;

use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Class Acq Checkout Com Commands.
 *
 * @package Drupal\acq_checkoutcom\Commands
 */
class AcqCheckoutComCommands extends DrushCommands {

  /**
   * The api helper object.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $apiHelper;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * AcqCheckoutComCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   * @param \Drupal\acq_checkoutcom\ApiHelper $api_helper
   *   ApiHelper object.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger_factory,
    ApiHelper $api_helper
  ) {
    $this->apiHelper = $api_helper;
    $this->logger = $logger_factory->get('acq_checkoutcom');
  }

  /**
   * Syncs and resets checkout.com config cache.
   *
   * @command acq_checkoutcom:sync-config
   *
   * @aliases sync-checkout-com-config
   */
  public function syncCheckoutComConfig() {
    // Reset magento checkout.com config cache.
    $this->apiHelper->getCheckoutcomConfig(NULL, TRUE);
    $this->logger->notice('checkout.com config info cache reset.');
  }

}
