<?php

namespace Drupal\alshaya_acm\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Provides a 'CheckoutSummaryBlock' block.
 *
 * @Block(
 *   id = "checkout_summary_block",
 *   admin_label = @Translation("Checkout Summary Block"),
 * )
 */
class CheckoutSummaryBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    // Load the CheckoutFlow plugin.
    $config = \Drupal::config('acq_checkout.settings');
    $checkout_flow_plugin = $config->get('checkout_flow_plugin') ?: 'multistep_default';
    $plugin_manager = \Drupal::service('plugin.manager.acq_checkout_flow');
    $checkout_flow = $plugin_manager->createInstance($checkout_flow_plugin, []);

    // Get the current step.
    $current_step_id = $checkout_flow->getStepId();

    if ($current_step_id == 'login' || $current_step_id == 'confirmation') {
      return [];
    }

    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

    $cart = $this->cartStorage->getCart();
    $items = $cart->items();

    // Products and No.of items.
    $products = [];
    $cart_count = 0;

    foreach ($items as $item) {
      // Load the first image.
      $image = alshaya_acm_get_product_display_image($item['sku'], 'checkout_summary_block_thumbnail');

      $node = alshaya_acm_product_get_display_node($item['sku']);
      $sku_attributes = alshaya_acm_product_get_sku_configurable_values($item['sku']);

      $item['name'] = [
        '#theme' => 'alshaya_cart_product_name',
        '#sku_attributes' => $sku_attributes,
        '#name' => [
          '#title' => $node->getTitle(),
          '#type' => 'link',
          '#url' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()]),
        ],
        '#image' => NULL,
        '#total_price' => NULL,
        '#item_code' => NULL,
      ];

      // Create products array to be used in twig.
      $products[] = [
        'name' => $item['name'],
        'image' => $image,
        'qty' => $item['qty'],
        'raw_total' => $item['price'],
        'total' => alshaya_acm_price_format($item['price']),
      ];

      // Total number of items in the cart.
      $cart_count += $item['qty'];
    }

    $delivery = [];

    // @TODO: Pending development for CnC.
    if ($method = $cart->getShippingMethodAsString()) {
      \Drupal::moduleHandler()->loadInclude('alshaya_acm_checkout', 'inc', 'alshaya_acm_checkout.shipping');

      // URL to change delivery address or shipping method.
      $options = ['absolute' => TRUE];
      $delivery['url'] = Url::fromRoute('acq_checkout.form', ['step' => 'shipping'], $options)->toString();

      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = alshaya_acm_checkout_load_shipping_method($method);
      $delivery['label'] = $this->t("Home Delivery");
      $delivery['address_label'] = $this->t("Delivery Address");

      $delivery['method_name'] = $term->getName();
      $delivery['method_description'] = $term->get('field_shipping_method_desc')->getString();

      // Delivery address.
      $shipping_address = (object) $cart->getShipping();

      $delivery['address'] = $shipping_address->street . ', ';

      // @TODO: Need to update this after address form/fields are changed.
      $delivery['address'] .= !empty($shipping_address->street2) ? $shipping_address->street2 . ', ' : '';
      $delivery['address'] .= !empty($shipping_address->city) ? $shipping_address->city . ', ' : '';
      $delivery['address'] .= !empty($shipping_address->region) ? $shipping_address->region . ', ' : '';
      $delivery['address'] .= $shipping_address->country . ', ';
      $delivery['address'] .= !empty($shipping_address->postcode) ? $shipping_address->postcode : '';
    }

    // Totals.
    $totals = [];
    $cart_totals = $cart->totals();

    // Subtotal.
    $totals['subtotal'] = alshaya_acm_price_format($cart_totals['sub']);

    // Tax.
    $totals['tax'] = (float) $cart_totals['tax'] > 0 ? alshaya_acm_price_format($cart_totals['tax']) : NULL;

    // Discount.
    $totals['discount'] = (float) $cart_totals['discount'] > 0 ? alshaya_acm_price_format($cart_totals['discount']) : NULL;

    // Shipping.
    $totals['shipping'] = (float) $cart_totals['shipping'] > 0 ? alshaya_acm_price_format($cart_totals['shipping']) : NULL;

    // Grand Total or Order total.
    $totals['grand'] = alshaya_acm_price_format($cart_totals['grand']);

    // Generate the cart link.
    $url = Url::fromRoute('acq_cart.cart')->toString();

    $build = [
      '#theme' => 'checkout_summary',
      '#cart_link' => $url,
      '#number_of_items' => $cart_count,
      '#products' => $products,
      '#totals' => $totals,
      '#delivery' => $delivery,
      '#attached' => [
        'library' => [
          'alshaya_acm/alshaya.acm.js',
          'core/jquery.ui.accordion',
        ],
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // We don't want this block to be cached since it needs to show the updated
    // details from the cart.
    return 0;
  }

}
