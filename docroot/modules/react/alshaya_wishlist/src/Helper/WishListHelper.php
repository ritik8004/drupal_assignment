<?php

namespace Drupal\alshaya_wishlist\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Utility\Token;

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
   * Current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Token manager.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $tokenManager;

  /**
   * WishListHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   *   The current user object.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path service.
   * @param \Drupal\Core\Utility\Token $token_manager
   *   Token manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    AccountProxyInterface $account_proxy,
    CurrentPathStack $current_path,
    Token $token_manager
  ) {
    $this->configFactory = $config_factory;
    $this->currentUser = $account_proxy;
    $this->currentPath = $current_path;
    $this->tokenManager = $token_manager;
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
      'localStorageExpirationForGuest' => $alshaya_wishlist_config->get('local_storage_expiration_guest'),
      'localStorageExpirationForLoggedIn' => $alshaya_wishlist_config->get('local_storage_expiration_logged_in'),
      'removeAfterAddtocart' => $alshaya_wishlist_config->get('remove_after_addtocart'),
      'enabledShare' => $alshaya_wishlist_config->get('enabled_share'),
      'shareEmailSubject' => $this->tokenManager->replace($alshaya_wishlist_config->get('email_subject')) ?? '',
      'shareEmailMessage' => $this->tokenManager->replace($alshaya_wishlist_config->get('email_template.value')) ?? '',
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
    return $this->configFactory->get('alshaya_wishlist.settings')->get('enabled');
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

  /**
   * Check if current page is wishlist product listing page.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isWishListPage() {
    $current_path = $this->currentPath->getPath();

    // Check if the wishlist path exist in the current path.
    if (strpos($current_path, '/wishlist') !== FALSE) {
      return TRUE;
    }

    // Return false always, if not a wishlist page.
    return FALSE;
  }

}
