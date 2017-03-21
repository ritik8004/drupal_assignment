<?php

/**
 * @file
 * Contains \Drupal\acq_cart\Plugin\Block\CartBlock.
 */

namespace Drupal\acq_cart\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Block\BlockBase;
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
   * Drupal\acq_cart\CartStorageInterface definition.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CartStorageInterface $cart_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cartStorage = $cart_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('acq_cart.cart_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cart = $this->cartStorage->getCart();
    $totals = $cart->totals();

    // The grand total including discounts and taxes.
    // @todo: Where to get the currency prefix from, the totals() is not
    // returning currency;
    $currency_format = 'KWD';
    $grand_total = $totals['grand'] >= 0 ? $totals['grand'] : 0;

    // The number of items in cart.
    $items = $this->cartStorage->getCart()->items();
    $quantity = 0;
    foreach ($items as $item) {
      $quantity += $item['qty'];
    }

    return([
      '#theme' => 'acq_cart_mini_cart',
      '#quantity' => $quantity,
      '#total' => $grand_total,
      '#currency_format' => $currency_format,
    ]);
  }
}
