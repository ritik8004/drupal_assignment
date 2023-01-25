<?php

namespace Drupal\alshaya_wishlist\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Utility\Token;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;

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
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    AccountProxyInterface $account_proxy,
    CurrentPathStack $current_path,
    Token $token_manager,
    LanguageManagerInterface $language_manager
  ) {
    $this->configFactory = $config_factory;
    $this->currentUser = $account_proxy;
    $this->currentPath = $current_path;
    $this->tokenManager = $token_manager;
    $this->languageManager = $language_manager;
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
      'disableQuickViewInWishlistPage' => $alshaya_wishlist_config->get('disable_quickview_wishlist_page'),
    ];

    // These configurations we need only for the logged in customers.
    if ($this->currentUser->isAuthenticated()) {
      // We need this setting for authenticate customers only as for anonymous
      // customers it should only load from local storage and for authenticate
      // customers either local storage or from magento based on this config.
      $config['forceLoadWishlistFromBackend'] = $alshaya_wishlist_config->get('force_load_wishlist_from_backend');

      // As wishlist share option only available for authenticate customers
      // we need these two settings for authenticate customers only.
      $config['shareEmailSubject'] = $this->tokenManager->replace($alshaya_wishlist_config->get('email_subject')) ?? '';
      $config['shareEmailMessage'] = $this->tokenManager->replace($alshaya_wishlist_config->get('email_template.value')) ?? '';

      // Check for the current language and if it's not english default,
      // we need to get the overidden configs and update them for a few
      // specific wishlist configs. This is because for VS and WES we were
      // not getting the language specific configs and thus it's causing
      // issues on the my wishlist page for the translations.
      // @todo this is, however, a temporary fix and we need to find out
      // the root cause of the problem behind this for VS and WES.
      $langCode = $this->languageManager->getCurrentLanguage()->getId();
      if ($langCode !== 'en') {
        $alshaya_wishlist_config = $this->languageManager->getLanguageConfigOverride($langCode, 'alshaya_wishlist.settings');
        $config['shareEmailSubject'] = $this->tokenManager->replace($alshaya_wishlist_config->get('email_subject')) ?? '';
        $config['shareEmailMessage'] = $this->tokenManager->replace($alshaya_wishlist_config->get('email_template.value')) ?? '';
      }
    }

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
    if (str_contains($current_path, '/wishlist')) {
      return TRUE;
    }

    // Return false always, if not a wishlist page.
    return FALSE;
  }

  /**
   * Determines whether to show/hide the wishlist icon.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node entity.
   * @param string $view_mode
   *   View mode value.
   *
   * @return bool
   *   True/false to show/hide wishlist icon.
   */
  public function showWishlistIconForProduct(NodeInterface $node, string $view_mode): bool {
    if (!in_array($view_mode, ['matchback', 'full', 'modal'])
      || !$this->isWishListEnabled()
    ) {
      return FALSE;
    }

    if ($view_mode === 'matchback'
      && !$this->configFactory->get('alshaya_wishlist.settings')->get('show_wishlist_on_matchback')) {
      return FALSE;
    }

    $product_pdp_layout = $this->configFactory->get('alshaya_acm_product.settings')->get('pdp_layout');
    $product_pdp_layout = $node->get('field_select_pdp_layout')->getString() ?? $product_pdp_layout;
    return $product_pdp_layout !== 'magazine_v2';
  }

}
