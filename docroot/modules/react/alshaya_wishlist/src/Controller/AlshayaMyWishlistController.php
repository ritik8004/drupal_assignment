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
   */
  public function __construct(
    WishListHelper $wishlist_helper,
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler,
    AlshayaAlgoliaReactConfig $algolia_config_helper
  ) {
    $this->wishListHelper = $wishlist_helper;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->algoliaConfigHelper = $algolia_config_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_wishlist.helper'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config')
    );
  }

  /**
   * Prepare wishlist page content.
   */
  public function wishList($context) {
    $cache_tags = [];

    $settings = [
      'enabled' => $this->wishListHelper->isWishListEnabled(),
      'config' => $this->wishListHelper->getWishListConfig(),
      'userDetails' => $this->wishListHelper->getWishListUserDetails(),
      'context' => $context,
    ];

    $cache_tags = Cache::mergeTags($cache_tags, $this->configFactory->get('alshaya_wishlist.settings')->getCacheTags());
    $this->moduleHandler->loadInclude('alshaya_wishlist', 'inc', 'alshaya_wishlist.static_strings');

    // Get the PLP algolia index from config.
    $page_type = AlshayaAlgoliaReactPLP::PAGE_TYPE;
    $page_sub_type = AlshayaAlgoliaReactPLP::PAGE_SUB_TYPE;
    $algoliaConfig = $this->algoliaConfigHelper->getAlgoliaReactCommonConfig($page_type, $page_sub_type);
    $settings['indexName'] = $algoliaConfig[$page_type]['indexName'];

    return [
      '#theme' => 'my_wishlist',
      '#strings' => _alshaya_wishlist_static_strings(),
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
        'tags' => $cache_tags,
      ],
    ];
  }

  /**
   * Returns page title.
   */
  public function getWishListTitle() {
    // @todo need to use wishlist_label token.
    return $this->t('My Wishlist');
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

}
