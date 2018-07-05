<?php

namespace Drupal\alshaya_acm_checkout\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\address\Repository\CountryRepository;
use Drupal\alshaya_acm\CartHelper;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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
   * Cart Helper service object.
   *
   * @var \Drupal\alshaya_acm\CartHelper
   */
  protected $cartHelper;

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Checkout Options Manager service object.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager
   */
  protected $checkoutOptionsManager;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ACQ Checkout Flow plugin manager object.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Store Finder Utility service object.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderUtility
   */
  protected $storesFinderUtility;

  /**
   * Address Country Repository service object.
   *
   * @var \Drupal\address\Repository\CountryRepository
   */
  protected $addressCountryRepository;

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
   * @param \Drupal\alshaya_acm\CartHelper $cart_helper
   *   Cart Helper service object.
   * @param \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager
   *   Checkout Options Manager service object.
   * @param \Drupal\address\Repository\CountryRepository $address_country_repository
   *   Address Country Repository service object.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   ACQ Checkout Flow plugin manager object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param mixed $store_finder_utility
   *   Store Finder Utility service object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              CartStorageInterface $cart_storage,
                              AlshayaAddressBookManager $address_book_manager,
                              CartHelper $cart_helper,
                              CheckoutOptionsManager $checkout_options_manager,
                              CountryRepository $address_country_repository,
                              PluginManagerInterface $plugin_manager,
                              ModuleHandlerInterface $module_handler,
                              RequestStack $request_stack,
                              $store_finder_utility) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->cartStorage = $cart_storage;
    $this->addressBookManager = $address_book_manager;
    $this->cartHelper = $cart_helper;
    $this->checkoutOptionsManager = $checkout_options_manager;
    $this->addressCountryRepository = $address_country_repository;
    $this->pluginManager = $plugin_manager;
    $this->moduleHandler = $module_handler;
    $this->requestStack = $request_stack;
    $this->storesFinderUtility = $store_finder_utility;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $store_finder_utility = NULL;

    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler */
    $moduleHandler = $container->get('module_handler');
    if ($moduleHandler->moduleExists('alshaya_stores_finder')) {
      $store_finder_utility = $container->get('alshaya_stores_finder_transac.utility');
    }

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('acq_cart.cart_storage'),
      $container->get('alshaya_addressbook.manager'),
      $container->get('alshaya_acm.cart_helper'),
      $container->get('alshaya_acm_checkout.options_manager'),
      $container->get('address.country_repository'),
      $container->get('plugin.manager.acq_checkout_flow'),
      $moduleHandler,
      $container->get('request_stack'),
      $store_finder_utility
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Load the CheckoutFlow plugin.
    $config = $this->configFactory->get('acq_checkout.settings');
    $acm_config = $this->configFactory->get('alshaya_acm_checkout.settings');
    $checkout_flow_plugin = $config->get('checkout_flow_plugin') ?: 'multistep_default';
    $checkout_flow = $this->pluginManager->createInstance($checkout_flow_plugin, []);

    // Get the current step.
    $current_step_id = $checkout_flow->getStepId();

    if ($current_step_id == 'login' || $current_step_id == 'confirmation') {
      return [];
    }

    $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

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
      $method = $this->checkoutOptionsManager->getCleanShippingMethodCode($method);

      $term = $this->checkoutOptionsManager->loadShippingMethod($method);

      $method_code = $term->get('field_shipping_code')->getString();

      $method_query_code = ($this->storesFinderUtility && $method_code == $this->checkoutOptionsManager->getClickandColectShippingMethod()) ? 'cc' : 'hd';

      // If we have shipping method in cart different than what currently we
      // have selected from checkout page, then remove the delivery info from
      // the display/block as causing confusion for the user.
      $shipping_method_from_url = $this->requestStack->getCurrentRequest()->get('method');
      if ($shipping_method_from_url && ($shipping_method_from_url !== $method_query_code)) {
        $delivery = [];
      }
      elseif ($method_query_code == 'cc') {
        if ($store_code = $cart->getExtension('store_code')) {
          // Not injected here to avoid module dependency.
          $store = $this->storesFinderUtility->getTranslatedStoreFromCode($store_code);

          $delivery['label'] = $this->t('Click & Collect');
          $delivery['method_name'] = '';

          if ($cart->getExtension('click_and_collect_type') == 'ship_to_store') {
            $duration = $store->get('field_store_sts_label')->getString();
          }
          else {
            $cc_settings = $this->configFactory->get('alshaya_click_collect.settings');
            $duration = $cc_settings->get('click_collect_rnc');
          }

          $delivery['method_description'] = $this->t('Your order will be available in @duration', ['@duration' => $duration]);
          $delivery['address_label'] = $this->t('Collection Store');

          $delivery['address'] = $this->storesFinderUtility->getStoreAddress($store);
        }
        else {
          $delivery = [];
        }
      }
      else {
        $delivery['label'] = $this->t('Home Delivery');
        $delivery['method_name'] = $term->getName();
        $delivery['method_description'] = $term->get('field_shipping_method_cart_desc')->getString();
        $delivery['address_label'] = $this->t('Delivery Address');

        // Delivery address.
        $shipping_address = $this->cartHelper->getShipping($cart);

        // Loading address from address book if customer_address_id is available
        // even if other values are set.
        if (isset($shipping_address['customer_address_id'])) {
          if ($entity = $this->addressBookManager->getUserAddressByCommerceId($shipping_address['customer_address_id'])) {
            $shipping_address = $this->addressBookManager->getAddressFromEntity($entity);
          }
        }

        $shipping_address = $this->addressBookManager->getAddressArrayFromMagentoAddress($shipping_address);

        $comma = $this->t(',')->render();

        if (!empty($shipping_address['address_line1'])) {
          $line1[] = $shipping_address['address_line2'];
          $line1[] = $shipping_address['dependent_locality'];

          if (!empty($shipping_address['locality'])) {
            $line2[] = $shipping_address['locality'] . $comma;
          }

          $line2[] = $shipping_address['address_line1'];

          if (!empty($shipping_address['area_parent_display'])) {
            $line2[] = $shipping_address['area_parent_display'] . $comma;
          }

          if (!empty($shipping_address['administrative_area_display'])) {
            $line2[] = $this->t('@area Area', ['@area' => $shipping_address['administrative_area_display']]);
          }
          elseif (!empty($shipping_address['administrative_area'])) {
            $line2[] = $this->t('@area Area', ['@area' => $shipping_address['administrative_area']]);
          }

          $country_list = $this->addressCountryRepository->getList();
          $line3[] = $country_list[$shipping_address['country_code']];

          $delivery_address = implode($comma . '<br>', [
            implode(' ', $line1),
            implode(' ', $line2),
            implode(' ', $line3),
          ]);
          $delivery['address'] = [
            '#markup' => $delivery_address,
          ];
        }
      }

      if (!empty($delivery)) {
        // URL to change delivery address or shipping method.
        $options = ['absolute' => TRUE];
        $delivery['change_url'] = Url::fromRoute('acq_checkout.form', ['step' => 'delivery'], $options)->toString();
        $edit_url = Url::fromRoute('acq_checkout.form', ['step' => 'delivery'], $options);
        $edit_url->setRouteParameter('method', $method_query_code);
        $delivery['edit_url'] = $edit_url->toString();
      }
    }

    // Totals.
    $totals = [];
    $cart_totals = $cart->totals();

    // Subtotal.
    $totals['subtotal'] = alshaya_acm_price_format($cart_totals['sub']);

    // Tax.
    $tax_config = $acm_config->get('checkout_show_tax_info');
    // Show tax info only if set to true.
    if ($tax_config) {
      $totals['tax'] = (float) $cart_totals['tax'] > 0 ? alshaya_acm_price_format($cart_totals['tax']) : NULL;
    }

    // Discount.
    $totals['discount'] = ((float) ($cart_totals['discount'])) != 0 ? alshaya_acm_price_format($cart_totals['discount']) : NULL;

    // Shipping.
    $totals['shipping'] = (float) $cart_totals['shipping'] > 0 ? alshaya_acm_price_format($cart_totals['shipping']) : NULL;

    // COD Surcharge.
    $surcharge_label = '';

    // We show surcharge only on payment page.
    if ($current_step_id == 'payment') {
      $surcharge = $cart->getExtension('surcharge');
      if ($surcharge && isset($surcharge['is_applied']) && $surcharge['is_applied']) {
        if ((float) $surcharge['amount'] > 0) {
          $surcharge_label = $acm_config->get('cod_surcharge_label');

          $surcharge_tooltip = $acm_config->get('cod_surcharge_tooltip');
          $totals['surcharge']['#markup'] = alshaya_acm_price_format($surcharge['amount'], [], $surcharge_tooltip);
        }
      }
    }

    // Grand Total or Order total.
    $totals['grand'] = alshaya_acm_price_format($cart_totals['grand']);

    // Generate the cart link.
    $url = Url::fromRoute('acq_cart.cart')->toString();

    // Adding vat text to summary block.
    $vat_text = $this->configFactory->get('alshaya_acm_product.settings')->get('vat_text');

    $build = [
      '#theme' => 'checkout_summary',
      '#cart_link' => $url,
      '#number_of_items' => $cart_count,
      '#products' => $products,
      '#surcharge_label' => $surcharge_label,
      '#totals' => $totals,
      '#delivery' => $delivery,
      '#vat_text' => $vat_text,
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
  public function getCacheContexts() {
    // Vary based on cart id and route.
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'cookies:Drupal_visitor_acq_cart_id',
      'route',
    ]);
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
