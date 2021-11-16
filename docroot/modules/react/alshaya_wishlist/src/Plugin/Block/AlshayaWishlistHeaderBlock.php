<?php

namespace Drupal\alshaya_wishlist\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_wishlist\Helper\WishListHelper;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'AlshayaWishlistHeaderBlock' block.
 *
 * @Block(
 *   id = "alshaya_react_wishlist_header",
 *   admin_label = @Translation("Alshaya Wishlist Header Block"),
 * )
 */
class AlshayaWishlistHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Wishlist Helper.
   *
   * @var \Drupal\alshaya_wishlist\Helper\WishListHelper
   */
  protected $wishListHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('alshaya_wishlist.wishlist_helper'),
    );
  }

  /**
   * AlshayaMiniWishlistBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\alshaya_wishlist\Helper\WishListHelper $wishlist_helper
   *   Wishlist Helper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              WishListHelper $wishlist_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->wishListHelper = $wishlist_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cache_tags = [];
    $build = [];
    // Return empty block if wishlist not enabled.
    if (!($this->wishListHelper->isWishListEnabled())) {
      return $build;
    }
    $settings = [
      'config' => $this->wishListHelper->getWishListConfig(),
      'userDetails' => $this->wishListHelper->getWishListUserDetails(),
    ];

    $cache_tags = Cache::mergeTags($cache_tags, $this->configFactory->get('alshaya_wishlist.settings')->getCacheTags());

    $build = [
      '#type' => 'markup',
      '#markup' => '<div id="wishlist-header-wrapper"></div><div id="wishlist_notification"></div>',
      '#attached' => [
        'drupalSettings' => [
          'wishlist' => $settings,
        ],
        'library' => [
          'alshaya_wishlist/wishlistheader',
        ],
      ],
      '#cache' => [
        'contexts' => [
          'languages:' . LanguageInterface::TYPE_INTERFACE,
          'user',
        ],
        'tags' => $cache_tags,
      ],
    ];

    return $build;
  }

}
