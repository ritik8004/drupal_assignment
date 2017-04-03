<?php

namespace Drupal\alshaya_acm\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\acq_sku\Entity\SKU;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

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
    $cart = $this->cartStorage->getCart();
    $items = $cart->items();

    // URL to change delivery address or shipping method.
    $options = ['absolute' => TRUE];
    $shipping_url = Url::fromRoute('acq_checkout.form', ['step' => 'shipping'], $options);
    $shipping_url = $shipping_url->toString();

    // @todo: Delivery type is Home delivery or Click and collect.
    // We should get the type from cartStorage object.
    $type = 'hd';

    if ($type == 'hd') {
      $delivery_label = $this->t("Home Delivery");
      $address_label = $this->t("Delivery Address");
    }
    else {
      $delivery_label = $this->t("Click & Collect");
      $address_label = $this->t("Collection Store");
    }

    // Shipping method & carrier.
    $shipping_method = $cart->getShippingMethod();
    $carrier_code = $shipping_method['carrier_code'];
    $method_code = $shipping_method['method_code'];
    $shipping_method_string = $this->t('@method by @carrier',
      ['@method' => $method_code, '@carrier' => $carrier_code]
    );

    // Delivery address.
    $shipping_address = $cart->getShipping();
    $shipping_address_string = isset($shipping_address->street) ?
      $shipping_address->street . ', ' : '';
    $shipping_address_string .= isset($shipping_address->street2) ?
      $shipping_address->street2 . ', ' : '';
    $shipping_address_string .= isset($shipping_address->city) ?
      $shipping_address->city . ', ' : '';
    $shipping_address_string .= isset($shipping_address->region) ?
      $shipping_address->region . ', ' : '';
    $shipping_address_string .= isset($shipping_address->country) ?
      $shipping_address->country . ', ' : '';
    $shipping_address_string .= isset($shipping_address->postcode) ?
      $shipping_address->postcode : '';

    // Fetch the config.
    $config = \Drupal::configFactory()
      ->get('acq_commerce.currency');

    // Fetch the currency format from the config factor.
    $currency_format = $config->get('currency_code');

    // Fetch the currency code position.
    $currency_code_position = $config->get('currency_code_position');

    // Products and No.of items.
    $products = [];
    $cart_count = 0;

    foreach ($items as $item) {
      $img = '';
      // Create image path.
      $image = SKU::loadFromSKU($item['sku'])->get('attr_image')->getValue();
      // If we have image for the product.
      if ($image != NULL) {
        $file_uri = File::load($image[0]['target_id'])->getFileUri();
        $img = ImageStyle::load('checkout_summary_block_thumbnail')->buildUrl($file_uri);
      }
      // Create products array to be used in twig.
      $products[] = [
        'name' => $item['name'],
        'imgurl' => $img,
        'qty' => $item['qty'],
        'total' => $item['price'],
      ];

      // Total number of items in the cart.
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
      '#theme' => 'checkout_summary',
      '#cart_link' => $url,
      '#number_of_items' => $cart_count,
      '#products' => $products,
      '#subtotal' => $subtotal,
      '#tax' => $tax,
      '#discount' => $discount,
      '#ordertotal' => $order_total,
      '#currency_format' => $currency_format,
      '#currency_code_position' => $currency_code_position,
      '#delivery_address' => $shipping_address_string,
      '#delivery_method' => $shipping_method_string,
      '#delivery_label' => $delivery_label,
      '#address_label' => $address_label,
      '#shipping_url' => $shipping_url,
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
    return 0;
  }

}
