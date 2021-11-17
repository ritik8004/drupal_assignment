<?php

namespace Drupal\alshaya_wishlist\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountProxyInterface;

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
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * WishListHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   *   The current user object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    AccountProxyInterface $account_proxy
  ) {
    $this->configFactory = $config_factory;
    $this->currentUser = $account_proxy;
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
      'wishlistinfoStorageExpirationForGuest' => $alshaya_wishlist_config->get('wishlistinfo_local_storage_expiration_guest'),
      'wishlistinfoStorageExpirationForLoggedIn' => $alshaya_wishlist_config->get('wishlistinfo_local_storage_expiration_logged_in'),
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

  /**
   * Get wishlist user details.
   *
   * @return array
   *   WishList user details.
   */
  public function getWishListUserDetails() {
    $user_details = [
      'id' => $this->currentUser->id(),
    ];

    return $user_details;
  }

}
