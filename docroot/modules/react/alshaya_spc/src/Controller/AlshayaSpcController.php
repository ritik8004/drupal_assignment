<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\alshaya_spc\Helper\AlshayaSpcOrderHelper;
use Drupal\alshaya_spc\Plugin\SpcPaymentMethod\CashOnDelivery;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\Core\Language\LanguageInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\alshaya_addressbook\AddressBookAreasTermsHelper;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AlshayaSpcController.
 */
class AlshayaSpcController extends ControllerBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Chekcout option manager.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager
   */
  protected $checkoutOptionManager;

  /**
   * Payment method.
   *
   * @var \Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager
   */
  protected $paymentMethodManager;

  /**
   * Mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Address terms helper.
   *
   * @var \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper
   */
  protected $areaTermsHelper;

  /**
   * Helper class for order processing.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcOrderHelper
   */
  protected $orderHelper;

  /**
   * AlshayaSpcController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager $payment_method_manager
   *   Payment method manager.
   * @param \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager
   *   Checkout option manager.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper $areas_term_helper
   *   Address terms helper.
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcOrderHelper $order_helper
   *   Order details helper.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              AlshayaSpcPaymentMethodManager $payment_method_manager,
                              CheckoutOptionsManager $checkout_options_manager,
                              MobileNumberUtilInterface $mobile_util,
                              AccountProxyInterface $current_user,
                              EntityTypeManagerInterface $entity_type_manager,
                              AddressBookAreasTermsHelper $areas_term_helper,
                              AlshayaSpcOrderHelper $order_helper) {
    $this->configFactory = $config_factory;
    $this->checkoutOptionManager = $checkout_options_manager;
    $this->paymentMethodManager = $payment_method_manager;
    $this->mobileUtil = $mobile_util;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->areaTermsHelper = $areas_term_helper;
    $this->orderHelper = $order_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.alshaya_spc_payment_method'),
      $container->get('alshaya_acm_checkout.options_manager'),
      $container->get('mobile_number.util'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_addressbook.area_terms_helper'),
      $container->get('alshaya_spc.order_helper')
    );
  }

  /**
   * Overridden controller for cart page.
   *
   * @return array
   *   Markup for cart page.
   */
  public function cart() {
    return [
      '#type' => 'markup',
      '#markup' => '<div id="spc-cart"></div>',
      '#attached' => [
        'library' => [
          'alshaya_spc/cart',
          'alshaya_spc/cart-sticky-header',
          'alshaya_white_label/spc-cart',
        ],
      ],
    ];
  }

  /**
   * Checkout pages.
   *
   * @return array
   *   Markup for checkout page.
   */
  public function checkout() {
    $cache_tags = [];
    $strings = [];

    $cc_config = $this->configFactory->get('alshaya_click_collect.settings');
    $cache_tags = Cache::mergeTags($cache_tags, $cc_config->getCacheTags());

    $checkout_settings = $this->configFactory->get('alshaya_acm_checkout.settings');
    $cache_tags = Cache::mergeTags($cache_tags, $checkout_settings->getCacheTags());

    $cncTerm = $this->checkoutOptionManager->getClickandColectShippingMethodTerm();
    $cache_tags = Cache::mergeTags($cache_tags, $cncTerm->getCacheTags());

    $user_name = [];
    // If logged in user.
    if ($this->currentUser->isAuthenticated()) {
      $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
      $cache_tags = Cache::mergeTags($cache_tags, $user->getCacheTags());

      $user_name = [
        'fname' => $user->get('field_first_name')->first()->getString(),
        'lname' => $user->get('field_last_name')->first()->getString(),
      ];

      $default_profile = $this->entityTypeManager->getStorage('profile')
        ->loadDefaultByUser($user, 'address_book');
      if ($default_profile) {
        $user_name['address_available'] = TRUE;
      }
    }

    $areas = [];
    $parent_list = $this->areaTermsHelper->getAllGovernates(TRUE);
    if (!empty($parent_list)) {
      foreach ($parent_list as $location_id => $location_name) {
        $child_areas = $this->areaTermsHelper->getAllAreasWithParent($location_id, TRUE);
        $areas[$location_id] = [
          'name' => $location_name,
          'areas' => $child_areas,
        ];
      }
    }

    // Get country code.
    $country_code = _alshaya_custom_get_site_level_country_code();

    $store_finder_config = $this->configFactory->get('alshaya_stores_finder.settings');
    $geolocation_config = $this->configFactory->get('geolocation.settings');
    $cache_tags = Cache::mergeTags($cache_tags, array_merge($store_finder_config->getCacheTags(), $geolocation_config->getCacheTags()));

    $strings[] = [
      'key' => 'find_your_nearest_store',
      'value' => $this->t('find your nearest store'),
    ];

    $strings[] = [
      'key' => 'select_this_store',
      'value' => $this->t('select this store'),
    ];

    $strings[] = [
      'key' => 'collection_store',
      'value' => $this->t('Collection Store'),
    ];

    $strings[] = [
      'key' => 'dismiss',
      'value' => $this->t('Dismiss'),
    ];

    $strings[] = [
      'key' => 'location_access_denied',
      'value' => $this->t('Access to your location access has been denied by your browser. You can reenable location services in your browser settings.'),
    ];

    $strings[] = [
      'key' => 'no_store_found',
      'value' => $this->t('Sorry, No store found for your location.'),
    ];

    $build = [
      '#theme' => 'spc_checkout',
      '#areas' => $areas,
      '#strings' => $strings,
      '#attached' => [
        'library' => [
          'alshaya_spc/googlemapapi',
          'alshaya_spc/checkout',
          'alshaya_white_label/spc-checkout',
        ],
        'drupalSettings' => [
          'cnc_subtitle_available' => $cc_config->get('checkout_click_collect_available'),
          'cnc_subtitle_unavailable' => $cc_config->get('checkout_click_collect_unavailable'),
          'terms_condition' => $checkout_settings->get('checkout_terms_condition.value'),
          'address_fields' => _alshaya_spc_get_address_fields(),
          'country_code' => $country_code,
          'country_mobile_code' => $this->mobileUtil->getCountryCode($country_code),
          'user_name' => $user_name,
          'mobile_maxlength' => $this->config('alshaya_master.mobile_number_settings')->get('maxlength'),
          'google_field_mapping' => $this->configFactory->get('alshaya_spc.google_mapping')->get('mapping'),
          'map' => [
            'google_api_key' => $geolocation_config->get('google_map_api_key'),
            'center' => $store_finder_config->get('country_center'),
            'placeholder' => $store_finder_config->get('store_search_placeholder'),
            'map_marker' => $store_finder_config->get('map_marker'),
            'cnc_shipping' => [
              'code' => $cncTerm->get('field_shipping_carrier_code')->getString(),
              'method' => $cncTerm->get('field_shipping_method_code')->getString(),
            ],
          ],
        ],
      ],
      '#cache' => [
        'contexts' => [
          'languages:' . LanguageInterface::TYPE_INTERFACE,
          'user',
        ],
        'tags' => $cache_tags,
      ],
    ];

    // Get payment methods.
    $payment_methods = [];
    $exclude_payment_methods = array_filter($checkout_settings->get('exclude_payment_methods'));
    foreach ($this->paymentMethodManager->getDefinitions() ?? [] as $payment_method) {
      // Avoid displaying the excluded methods.
      if (isset($exclude_payment_methods[$payment_method['id']])) {
        continue;
      }

      /** @var \Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase $plugin */
      $plugin = $this->paymentMethodManager->createInstance($payment_method['id']);
      $plugin->processBuild($build);

      $payment_method_term = $this->checkoutOptionManager->loadPaymentMethod($payment_method['id']);

      $payment_methods[$payment_method['id']] = [
        'name' => $payment_method_term->label(),
        'description' => $payment_method_term->getDescription(),
        'code' => $payment_method_term->get('field_payment_code')->getString(),
        'default' => ($payment_method_term->get('field_payment_default')->getString() == '1'),
        'weight' => $payment_method_term->getWeight(),
      ];

      // Show default on top.
      $payment_methods[$payment_method['id']]['weight'] = $payment_methods[$payment_method['id']]['default']
        ? -999
        : (int) $payment_method_term->getWeight();
    }

    array_multisort(array_column($payment_methods, 'weight'), SORT_ASC, $payment_methods);
    $build['#attached']['drupalSettings']['payment_methods'] = $payment_methods;

    return $build;
  }

  /**
   * Checkout Confirmation page.
   *
   * @return array
   *   Markup for checkout confirmation page.
   */
  public function checkoutConfirmation() {
    $strings = [];

    $order = $this->orderHelper->getLastOrder();
    // Get order type hd/cnc and other details.
    $orderDetails = $this->orderHelper->getOrderDetails($order);

    // Get formatted customer phone number.
    $phone_number = $this->orderHelper->getFormattedMobileNumber($order['shipping']['address']['telephone']);

    // Order Totals.
    $totals = [
      'subtotal_incl_tax' => $order['totals']['sub'],
      'shipping_incl_tax' => $order['totals']['shipping'],
      'base_grand_total' => $order['totals']['grand'],
      'discount_amount' => $order['totals']['discount'],
      'free_delivery' => 'false',
      'surcharge' => $order['totals']['surcharge'],
    ];

    // Get Products.
    $productList = [];
    foreach ($order['items'] as $item) {
      if (in_array($item['sku'], array_keys($productList))) {
        continue;
      }
      // Populate price and other info from order response data.
      $productList[$item['sku']] = $this->orderHelper->getSkuDetails($item);
    }

    $checkout_settings = $this->configFactory->get('alshaya_acm_checkout.settings');

    $settings = [
      'site_details' => [
        'logo' => theme_get_setting('logo.url'),
        'customer_service_text' => $checkout_settings->get('checkout_customer_service'),
      ],
      'order_details' => [
        'customer_email' => $order['email'],
        'order_number' => $order['increment_id'],
        'customer_name' => $order['firstname'] . ' ' . $order['lastname'],
        'mobile_number' => $phone_number,
        'expected_delivery' => $orderDetails['delivery_method_description'],
        'number_of_items' => count($productList),
        'delivery_type_info' => $orderDetails,
        'totals' => $totals,
        'items' => $productList,
        'payment' => $orderDetails['payment'],
        'billing' => $orderDetails['billing'],
      ],
    ];

    if ($orderDetails['payment']['methodCode'] === 'cashondelivery') {
      $strings = array_merge($strings, CashOnDelivery::getCodSurchargeStrings());
    }

    $cache_tags = [];
    $cache_tags = Cache::mergeTags($cache_tags, $checkout_settings->getCacheTags());

    // If logged in user.
    if ($this->currentUser->isAuthenticated()) {
      $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
      $cache_tags = Cache::mergeTags($cache_tags, $user->getCacheTags());
    }

    return [
      '#theme' => 'spc_confirmation',
      '#strings' => $strings,
      '#attached' => [
        'library' => [
          'alshaya_spc/checkout-confirmation',
          'alshaya_white_label/spc-checkout-confirmation',
        ],
        'drupalSettings' => $settings,
      ],
      '#cache' => [
        'contexts' => [
          'languages:' . LanguageInterface::TYPE_INTERFACE,
          'session',
        ],
        'tags' => $cache_tags,
      ],
    ];
  }

  /**
   * Verifies the mobile number.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function validateInfo(Request $request) {
    $data = $request->getContent();
    if (!empty($data)) {
      $data = json_decode($data, TRUE);
    }

    if (empty($data)) {
      return new JsonResponse(['status' => FALSE]);
    }

    $status = [];

    foreach ($data as $key => $value) {
      $status[$key] = FALSE;

      switch ($key) {
        case 'mobile':
          $country_code = _alshaya_custom_get_site_level_country_code();
          $country_mobile_code = '+' . $this->mobileUtil->getCountryCode($country_code);

          if (strpos($value, $country_mobile_code) === FALSE) {
            $value = $country_mobile_code . $value;
          }

          try {
            if ($this->mobileUtil->testMobileNumber($value)) {
              $status[$key] = TRUE;
            }
          }
          catch (\Exception $e) {
            $status[$key] = FALSE;
          }

          break;

        case 'email':
          // For email validation we do two checks:
          // 1. email domain is valid
          // 2. email is not of an existing customer.
          $domain = explode('@', $value)[1];
          $dns_records = dns_get_record($domain);
          if (empty($dns_records)) {
            $status[$key] = 'invalid';
          }
          else {
            $user = user_load_by_mail($value);
            $status[$key] = ($user instanceof UserInterface) ? 'exists' : '';
          }

          break;
      }
    }

    return new JsonResponse(['status' => TRUE] + $status);
  }

}
