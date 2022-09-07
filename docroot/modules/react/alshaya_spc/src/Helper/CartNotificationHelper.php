<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Helper class for Hello Member.
 *
 * @package Drupal\alshaya_spc\Helper
 */
class CartNotificationHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Cart notification helper constructor.
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
   * Helper to check if Hello Member is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isCartDrawerEnabled() {
    return $this->getConfig()->get('notification_drawer');
  }

  /**
   * Wrapper function to get Cart notification Config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Cart notification config.
   */
  public function getConfig() {
    static $config;

    if (is_null($config)) {
      $config = $this->configFactory->get('alshaya_spc.cart_notification');
    }

    return $config;
  }

}
