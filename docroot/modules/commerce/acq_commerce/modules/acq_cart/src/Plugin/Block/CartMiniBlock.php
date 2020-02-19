<?php

namespace Drupal\acq_cart\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_cart\MiniCartManager;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CartMiniBlock' block.
 *
 * @Block(
 *   id = "cart_mini_block",
 *   admin_label = @Translation("Cart Mini Block"),
 * )
 */
class CartMiniBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The cart session.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $currentUser;

  /**
   * Mini cart manager service.
   *
   * @var \Drupal\acq_cart\MiniCartManager
   */
  protected $miniCartManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CartStorageInterface $cart_storage, MiniCartManager $mini_cart_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cartStorage = $cart_storage;
    $this->miniCartManager = $mini_cart_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('acq_cart.cart_storage'),
      $container->get('acq_cart.mini_cart')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->miniCartManager->getMiniCart();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Cart will be different for every session, even guests will have session
    // as soon as they add something to cart.
    return Cache::mergeContexts(
      parent::getCacheContexts(),
      ['session', 'cookies:Drupal_visitor_acq_cart_id']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();

    // As soon as we have cart, we have session.
    // As soon as we have session, varnish is disabled.
    // We are good to have no cache tag based on cart if there is none.
    if ($cart = $this->cartStorage->getCart(FALSE)) {
      // Custom cache tag here will be cleared in API Wrapper after every
      // update cart call.
      $cache_tags = Cache::mergeTags($cache_tags, [
        'cart:' . $cart->id(),
      ]);
    }

    return $cache_tags;
  }

}
