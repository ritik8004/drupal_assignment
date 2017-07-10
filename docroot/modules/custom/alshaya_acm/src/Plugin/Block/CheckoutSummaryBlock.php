<?php

namespace Drupal\alshaya_acm\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
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
   * Address book manager.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  protected $addressBookManager;

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   Address book manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, CartStorageInterface $cart_storage, AlshayaAddressBookManager $address_book_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->cartStorage = $cart_storage;
    $this->addressBookManager = $address_book_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('acq_cart.cart_storage'),
      $container->get('alshaya_addressbook.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Load the CheckoutFlow plugin.
    $config = $this->configFactory->get('acq_checkout.settings');
    $checkout_flow_plugin = $config->get('checkout_flow_plugin') ?: 'multistep_default';
    $plugin_manager = \Drupal::service('plugin.manager.acq_checkout_flow');
    $checkout_flow = $plugin_manager->createInstance($checkout_flow_plugin, []);

    // Get the current step.
    $current_step_id = $checkout_flow->getStepId();

    $checkout_config = $this->configFactory->get('alshaya_acm_checkout.settings');

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

    if ($method = $cart->getShippingMethodAsString()) {
      $method = substr($method, 0, 32);

      // URL to change delivery address or shipping method.
      $options = ['absolute' => TRUE];
      $delivery['url'] = Url::fromRoute('acq_checkout.form', ['step' => 'delivery'], $options)->toString();

      /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
      $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');
      $term = $checkout_options_manager->loadShippingMethod($method);

      $method_code = $term->get('field_shipping_code')->getString();

      if ($method_code == $checkout_config->get('click_collect_method')) {
        $delivery['label'] = $this->t('Click & Collect');
        $delivery['method_name'] = '';
        $delivery['method_description'] = $term->get('field_shipping_method_desc')->getString();
        $delivery['address_label'] = $this->t('Collection Store');

        $store_code = $cart->getExtension('store_code');

        // Not injected here to avoid module dependency.
        $store = \Drupal::service('alshaya_stores_finder.utility')->getStoreFromCode($store_code);

        $delivery['address'] = $store->get('field_store_address')->getString();
      }
      else {
        $delivery['label'] = $this->t('Home Delivery');
        $delivery['method_name'] = $term->getName();
        $delivery['method_description'] = $term->get('field_shipping_method_desc')->getString();
        $delivery['address_label'] = $this->t('Delivery Address');

        // Delivery address.
        $shipping_address = (array) $cart->getShipping();

        // Loading address from address book if customer_address_id is available
        // even if other values are set.
        if (isset($shipping_address['customer_address_id'])) {
          if ($entity = $this->addressBookManager->getUserAddressByCommerceId($shipping_address['customer_address_id'])) {
            $shipping_address = $this->addressBookManager->getAddressFromEntity($entity);
          }
        }

        $shipping_address = $this->addressBookManager->getAddressArrayFromMagentoAddress($shipping_address);

        if (isset($shipping_address['address_line1'])) {
          $line1[] = $shipping_address['address_line2'];
          $line1[] = $shipping_address['dependent_locality'];

          $line2[] = $shipping_address['locality'] . ',';

          $line2[] = $shipping_address['address_line1'];
          $line2[] = $this->t('@area Area', ['@area' => _alshaya_addressbook_get_area_from_id($shipping_address['administrative_area'])]);

          $country_list = \Drupal::service('address.country_repository')->getList();
          $line3[] = $country_list[$shipping_address['country_code']];

          $delivery['address'] = implode(',<br>', [
            implode(' ', $line1),
            implode(' ', $line2),
            implode(' ', $line3),
          ]);
        }
      }
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
