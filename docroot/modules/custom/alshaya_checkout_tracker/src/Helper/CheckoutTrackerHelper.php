<?php

namespace Drupal\alshaya_checkout_tracker\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Helper class for Cehckout Tracker.
 *
 * @package Drupal\alshaya_checkout_tracker\Helper
 */
class CheckoutTrackerHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Checkout Tracker constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory
  ) {
    $this->configFactory = $config_factory;
  }

  /**
   * Helper to check if Checkout Tracker is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isCheckoutTrackerEnabled() {
    return $this->getConfig()->get('checkout_tracker_enabled');
  }

  /**
   * Helper to get Cache Tags for Checkout Tracker Config.
   *
   * @return string[]
   *   A set of cache tags.
   */
  public function getCacheTags() {
    return $this->getConfig()->getCacheTags();
  }

  /**
   * Wrapper function to get Checkout Tracker Config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Checkout Tracker Config.
   */
  public function getConfig() {
    static $config;

    if (is_null($config)) {
      $config = $this->configFactory->get('alshaya_checkout_tracker.settings');
    }

    return $config;
  }

}
