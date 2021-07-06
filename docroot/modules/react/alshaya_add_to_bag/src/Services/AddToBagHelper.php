<?php

namespace Drupal\alshaya_add_to_bag\Services;

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
   * Constructor for the AddToBagHelper service.
   *
   * @param Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory
  ) {
    $this->configFactory = $config_factory;
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
