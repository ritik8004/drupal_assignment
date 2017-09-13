<?php

namespace Drupal\acq_cart\Plugin\Block;

use Drupal\acq_cart\MiniCartManager;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
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
   * Current user service.
   *
   * @var AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Mini cart manager service.
   *
   * @var MiniCartManager
   */
  protected $miniCartManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_user, MiniCartManager $mini_cart_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
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
      $container->get('current_user'),
      $container->get('acq_cart.mini_cart')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Fetch mini cart block content.
    $mini_cart = $this->miniCartManager->getMiniCart();
    $mini_cart['#cache']['contexts'][] = 'cookies:Drupal_visitor_acq_cart_id';

    // Set cache metadata if cart_id is set.
    if (isset($mini_cart['cart_id'])) {
      $cart_id = $mini_cart['cart_id'];
      $mini_cart['#cache']['tags'][] = 'mini_cart_' . $cart_id;
      unset($mini_cart['cart_id']);
    }

    return $mini_cart;
  }

}
