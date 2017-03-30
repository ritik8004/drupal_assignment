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
use Drupal\Core\Url;

/**
 * Provides a 'CartBlock' block.
 *
 * @Block(
 *   id = "cart_block",
 *   admin_label = @Translation("Cart block"),
 * )
 */
class CartBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $items = $cart->items();

    // Fetch the config.
    $config = \Drupal::configFactory()
      ->get('acq_commerce.currency');

    // Fetch the currency format from the config factor.
    $currency_format = $config->get('currency_code');

    // Fetch the currency code position.
    $currency_code_position = $config->get('currency_code_position');

    // Image Url.
    // @todo: Real image from products.
    $img = 'http://www.israel-catalog.com/sites/default/files/imagecache/prod-small/products/images/israel-t-shirt-california-women.jpg';

    // Products and No.of items.
    $products = array();
    $cart_count = 0;

    foreach ($items as $item) {
      $products[] = [
        'name' => $item['name'],
        'imgurl' => $img,
        'qty' => $item['qty'],
        'total' => $item['price'],
      ];

      $cart_count += $item['qty'];
    }

    // Totals.
    $subtotal = $tax = $discount = 0;
    $totals = $cart->totals();

    // Subtotal.
    $subtotal = $totals['sub'];

    // Tax.
    if ((float) $totals['tax'] > 0) {
      $tax = $totals['tax'];
    }

    // Discount.
    if ((float) $totals['discount'] > 0) {
      $discount = $totals['discount'];
    }

    // Grand Total or Order total.
    $order_total = $totals['grand'];

    // Generate the cart link.
    $url = Url::fromRoute('acq_cart.cart')->toString();

    $build = [
      '#theme' => 'acq_cart_summary',
      '#cart_link' => $url,
      '#number_of_items' => $cart_count,
      '#products' => $products,
      '#subtotal' => $subtotal,
      '#tax' => $tax,
      '#discount' => $discount,
      '#ordertotal'=> $order_total,
      '#currency_format' => $currency_format,
      //@todo: Status would be dynmaic, adding static for now.
      '#order_status' => $this->t('Before delivery'),
      '#currency_code_position' => $currency_code_position,
      '#attached' => [
        'library' =>[
          'core/jquery.ui.accordion',
        ]
      ]
    ];

    return $build;
  }

}
