<?php

namespace Drupal\alshaya_wishlist\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Helper class for Wishlist.
 *
 * @package Drupal\alshaya_wishlist\Helper
 */
class WishListHelper {

  use StringTranslationTrait;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * WishListHelper constructor.
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
   * Get wishlist config.
   *
   * @return array
   *   WishList config.
   */
  public function getWishListConfig() {
    $alshaya_wishlist_config = $this->configFactory->get('alshaya_wishlist.settings');

    $config = [
      'emptyWishListMessage' => $alshaya_wishlist_config->get('empty_wishlist_message') ?? '',
    ];

    return $config;
  }

  /**
   * Helper to check if wishlist is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isWishListEnabled() {
    return $this->configFactory->get('alshaya_wishlist.settings')->get('wishlist_enabled');
  }

}
