<?php

namespace Drupal\alshaya_wishlist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\alshaya_wishlist\Helper\WishListHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfig;
use Drupal\alshaya_algolia_react\Plugin\Block\AlshayaAlgoliaReactPLP;
use Drupal\Core\Utility\Token;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountInterface;

/**
 * AlshayaMyWishlistController for wishlist page.
 *
 * @package Drupal\alshaya_wishlist\Controller
 */
class AlshayaMyWishlistController extends ControllerBase {
  /**
   * Wishlist Helper.
   *
   * @var \Drupal\alshaya_wishlist\Helper\WishListHelper
   */
  protected $wishListHelper;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Algolia React Config Helper.
   *
   * @var \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfig
   */
  protected $algoliaConfigHelper;

  /**
   * Token manager.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $tokenManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * AlshayaMyWishlistController constructor.
   *
   * @param \Drupal\alshaya_wishlist\Helper\WishListHelper $wishlist_helper
   *   Wishlist Helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module services.
   * @param \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfig $algolia_config_helper
   *   Algolia React Config Helper.
   * @param \Drupal\Core\Utility\Token $token_manager
   *   Token manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user object.
   */
  public function __construct(
    WishListHelper $wishlist_helper,
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler,
    AlshayaAlgoliaReactConfig $algolia_config_helper,
    Token $token_manager,
    AccountInterface $current_user
  ) {
    $this->wishListHelper = $wishlist_helper;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->algoliaConfigHelper = $algolia_config_helper;
    $this->tokenManager = $token_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_wishlist.helper'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config'),
      $container->get('token'),
      $container->get('current_user'),
    );
  }

  /**
   * Prepare wishlist page content.
   */
  public function wishList(Request $request, $context) {
    $cache_tags = [];

    $settings = [
      'enabled' => $this->wishListHelper->isWishListEnabled(),
      'config' => $this->wishListHelper->getWishListConfig(),
      'userDetails' => $this->wishListHelper->getWishListUserDetails(),
      'context' => $context,
    ];

    // If context is 'share' wishlist then pass query string data to settings.
    if ($context == 'share') {
      // Get the sharing code if available with query string.
      $data = json_decode(base64_decode($request->query->get('data')), NULL);
      $settings['sharedCode'] = $data->sharedCode ?? '';
    }

    $cache_tags = Cache::mergeTags($cache_tags, $this->configFactory->get('alshaya_wishlist.settings')->getCacheTags());
    $this->moduleHandler->loadInclude('alshaya_wishlist', 'inc', 'alshaya_wishlist.static_strings');

    // Get the PLP algolia index from config.
    $algoliaConfig = $this->algoliaConfigHelper->getAlgoliaReactCommonConfig(AlshayaAlgoliaReactPLP::PAGE_TYPE, AlshayaAlgoliaReactPLP::PAGE_SUB_TYPE);
    $settings['indexName'] = $algoliaConfig[AlshayaAlgoliaReactPLP::PAGE_TYPE]['indexName'];

    return [
      '#theme' => 'my_wishlist',
      '#attached' => [
        'drupalSettings' => [
          'wishlist' => $settings,
        ],
        'library' => [
          'alshaya_white_label/my-wishlist-page',
          'alshaya_wishlist/my-wishlist',
        ],
      ],
      '#cache' => [
        'contexts' => ['user'],
        'tags' => $cache_tags,
      ],
    ];
  }

  /**
   * Returns page title.
   */
  public function getWishListTitle(Request $request, $context) {
    // If context is 'share' wishlist then get the username from query string.
    if ($context == 'share') {
      // Get the user's name if available with query string.
      $data = json_decode(base64_decode($request->query->get('data')), NULL);
      $userName = isset($data->sharedUserName) ? ($data->sharedUserName . "'s") : "";

      return $this->t('@userName @wishlist_label', [
        '@userName' => $userName,
        '@wishlist_label' => $this->tokenManager->replace('[alshaya_wishlist:wishlist_label]'),
      ], ['context' => 'wishlist']);
    }

    return $this->t('my @wishlist_label', [
      '@wishlist_label' => $this->tokenManager->replace('[alshaya_wishlist:wishlist_label]'),
    ], ['context' => 'wishlist']);
  }

  /**
   * Helper method to check access.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Return access result object.
   */
  public function checkAccess() {
    return AccessResult::allowedIf($this->wishListHelper->isWishListEnabled());
  }

  /**
   * Helper method to check access for authenticate users.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Return access result object.
   */
  public function checkAuthenticateUser() {
    // Only logged-in users will be able to access my wishlist page if enabled.
    return AccessResult::allowedIf($this->wishListHelper->isWishListEnabled()
      && $this->currentUser->isAuthenticated());
  }

}
