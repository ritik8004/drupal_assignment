<?php

namespace Drupal\alshaya_wishlist\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_wishlist\Helper\WishListHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Utility\Token;

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
   * Token manager.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $tokenManager;

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
      $container->get('alshaya_wishlist.helper'),
      $container->get('token'),
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
   * @param \Drupal\Core\Utility\Token $token_manager
   *   Token manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              WishListHelper $wishlist_helper,
                              Token $token_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->wishListHelper = $wishlist_helper;
    $this->tokenManager = $token_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Return empty block if wishlist not enabled.
    if (!($this->wishListHelper->isWishListEnabled())) {
      return $build;
    }

    $settings = [
      'config' => $this->wishListHelper->getWishListConfig(),
      'wishlist_label' => $this->tokenManager->replace('[alshaya_wishlist:wishlist_label]'),
    ];

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
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'languages:' . LanguageInterface::TYPE_INTERFACE,
      'user',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), $this->configFactory->get('alshaya_wishlist.settings')->getCacheTags());
  }

}
