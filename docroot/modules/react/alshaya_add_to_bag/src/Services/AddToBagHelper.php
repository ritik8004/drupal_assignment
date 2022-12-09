<?php

namespace Drupal\alshaya_add_to_bag\Services;

use Drupal\acq_sku\CartFormHelper;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * General Helper service for the Add To Bag feature.
 */
class AddToBagHelper {

  /**
   * Config factory service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Cart form helper.
   *
   * @var \Drupal\acq_sku\CartFormHelper
   */
  protected $cartFormHelper;

  /**
   * Constructor for the AddToBagHelper service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\acq_sku\CartFormHelper $cart_form_helper
   *   Cart form helper.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    CartFormHelper $cart_form_helper
  ) {
    $this->configFactory = $config_factory;
    $this->cartFormHelper = $cart_form_helper;
  }

  /**
   * Detects if the Add To Bag feature is enabled or not.
   */
  public function isAddToBagFeatureEnabled() {
    return $this->configFactory->get('alshaya_add_to_bag.settings')->get('display_addtobag');
  }

  /**
   * Get product's info local storage expiration time.
   */
  public function getProductInfoLocalStorageExpiration() {
    return $this->configFactory->get('alshaya_add_to_bag.settings')->get('productinfo_local_storage_expiration');
  }

}
