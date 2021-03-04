<?php

namespace Drupal\alshaya_wishlist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\alshaya_wishlist\Helper\WishListHelper;
use Drupal\Core\Config\ConfigFactoryInterface;

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
   * AlshayaMyWishlistController constructor.
   *
   * @param \Drupal\alshaya_wishlist\Helper\WishListHelper $wishlist_helper
   *   Wishlist Helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module services.
   */
  public function __construct(
    WishListHelper $wishlist_helper,
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler
  ) {
    $this->wishListHelper = $wishlist_helper;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_wishlist.wishlist_helper'),
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * Prepare wishlist page content.
   */
  public function wishList() {
    $cache_tags = [];

    $settings = [
      'enabled' => $this->wishListHelper->isWishListEnabled(),
      'config' => $this->wishListHelper->getWishListConfig(),
    ];

    $cache_tags = Cache::mergeTags($cache_tags, $this->configFactory->get('alshaya_wishlist.settings')->getCacheTags());
    $this->moduleHandler->loadInclude('alshaya_wishlist', 'inc', 'alshaya_wishlist.static_strings');

    return [
      '#theme' => 'my_wishlist',
      '#strings' => _alshaya_wishlist_static_strings(),
      '#attached' => [
        'drupalSettings' => [
          'wishlist' => $settings,
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
    return $this->t('My Favourites');
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
