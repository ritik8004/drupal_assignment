<?php

namespace Drupal\alshaya_online_returns\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\acq_commerce\SKUInterface;

/**
 * Helper class for Online Returns.
 *
 * @package Drupal\alshaya_online_returns\Helper
 */
class OnlineReturnsHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * OnlineReturnsHelper constructor.
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
   * Helper to check if aura is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isOnlineReturnsEnabled() {
    return $this->configFactory->get('alshaya_online_returns.settings')->get('status');
  }

  /**
   * Wrapper function to check if SKU is returnable.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku_entity
   *   SKU entity object.
   *
   * @return bool
   *   SKU is returnable or not.
   */
  public function isSkuReturnable(SKUInterface $sku_entity) {
    $is_returnable = $sku_entity->get('attr_is_returnable')->getString();
    return ($is_returnable !== '') ? (bool) $is_returnable : TRUE;
  }

}
