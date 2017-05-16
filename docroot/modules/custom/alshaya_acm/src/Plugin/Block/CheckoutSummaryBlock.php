<?php

namespace Drupal\alshaya_acm\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
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
    $carrier_code = isset($shipping_method['carrier_code']) ?
      $shipping_method['carrier_code'] : NULL;

    $method_code = isset($shipping_method['method_code']) ?
      $shipping_method['method_code'] : NULL;

    // Display shipping method section only if a method is set.
    if ($method_code != NULL && $carrier_code != NULL) {
      $shipping_method_string = $this->t('@method by @carrier',
        ['@method' => $method_code, '@carrier' => $carrier_code]
      );
    }
    else {
      $shipping_method_string = NULL;
    }

    // Delivery address.
    $shipping_address = $cart->getShipping();

    // We check if address is set, we will use Address line 1 as our indicator
    // as it is a mandatory field.
    if (isset($shipping_address->street)) {
      $shipping_address_string = $shipping_address->street;

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
    }
    else {
      $shipping_address_string = NULL;
    }

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

      // Load sku from item_id that we have in $item.
      $media = alshaya_acm_product_get_sku_media($item['sku']);

      // If we have image for the product.
      if (!empty($media)) {
        $image = array_shift($media);
        if (is_object($image['file']) && $image['file'] instanceof FileInterface) {
          $file_uri = $image['file']->getFileUri();
          $img = ImageStyle::load('checkout_summary_block_thumbnail')->buildUrl($file_uri);
        }
      }

      // Check if we can find a parent SKU for this.
      $parent_sku = alshaya_acm_product_get_parent_sku_by_sku($item['sku']);

      if (is_object($parent_sku) && $parent_sku instanceof SKUInterface) {
        /* @var \Drupal\node\Entity\Node $parent_node */
        $parent_node = alshaya_acm_product_get_display_node($parent_sku->getSKU());
        if ($parent_node) {
          $item['name'] = [
            '#title' => $parent_node->getTitle(),
            '#type' => 'link',
            '#url' => Url::fromRoute('entity.node.canonical', ['node' => $parent_node->id()]),
          ];
        }
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
    // We don't want this block to be cached since it needs to show the updated
    // details from the cart.
    return 0;
  }

}
