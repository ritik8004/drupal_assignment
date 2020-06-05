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
use Drupal\Core\Language\LanguageManagerInterface;
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
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              AlshayaSpcPaymentMethodManager $payment_method_manager,
                              CheckoutOptionsManager $checkout_options_manager,
                              MobileNumberUtilInterface $mobile_util,
                              AccountProxyInterface $current_user,
                              EntityTypeManagerInterface $entity_type_manager,
                              AddressBookAreasTermsHelper $areas_term_helper,
                              AlshayaSpcOrderHelper $order_helper,
                              LanguageManagerInterface $language_manager) {
    $this->configFactory = $config_factory;
    $this->checkoutOptionManager = $checkout_options_manager;
    $this->paymentMethodManager = $payment_method_manager;
    $this->mobileUtil = $mobile_util;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->areaTermsHelper = $areas_term_helper;
    $this->orderHelper = $order_helper;
    $this->languageManager = $language_manager;
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
      $container->get('alshaya_spc.order_helper'),
      $container->get('language_manager')
    );
  }

  /**
   * Overridden controller for cart page.
   *
   * @return array
   *   Markup for cart page.
   */
  public function cart() {
    $acm_config = $this->configFactory->get('alshaya_acm.settings');
    $cache_tags = $acm_config->getCacheTags();

    $cart_config = $this->configFactory->get('alshaya_acm.cart_config');
    $cache_tags = Cache::mergeTags($cache_tags, $cart_config->getCacheTags());

    $build = [
      '#type' => 'markup',
      '#markup' => '<div id="spc-cart"></div>',
      '#attached' => [
        'library' => [
          'alshaya_spc/cart',
          'alshaya_spc/cart-sticky-header',
          'alshaya_white_label/spc-cart',
          'alshaya_acm_promotion/basket_labels_manager',
        ],
        'drupalSettings' => [
          'quantity_limit_enabled' => $acm_config->get('quantity_limit_enabled'),
          'alshaya_spc' => [
            'max_cart_qty' => $cart_config->get('max_cart_qty'),
          ],
        ],
      ],
      '#cache' => [
        'tags' => $cache_tags,
      ],
    ];

    return $this->addCheckoutConfigSettings($build);
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

      $user_mobile_number = $user->get('field_mobile_number')->first();
      $user_name = [
        'fname' => $user->get('field_first_name')->getString(),
        'lname' => $user->get('field_last_name')->getString(),
        'mobile' => !empty($user_mobile_number) ? $user_mobile_number->getValue()['local_number'] : '',
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

    $country_name = $this->mobileUtil->getCountryName($country_code);
    $strings[] = [
      'key' => 'location_outside_country_hd',
      'value' => '<span class="font-bold">' . $this->t('You are browsing outside @country', ['@country' => $country_name]) . '</span><br/>'
      . $this->t("We don't support delivery outside @country.", ['@country' => $country_name])
      . $this->t('Please enter an address with in country @country below to continue.', ['@country' => $country_name]),
    ];

    $cncFeatureStatus = $cc_config->get('feature_status') ?? 'enabled';
    $cnc_enabled = $cncFeatureStatus === 'enabled';
    if ($cnc_enabled) {
      $strings[] = [
        'key' => 'location_outside_country_cnc',
        'value' => '<span class="font-bold">' . $this->t('You are browsing outside @country', ['@country' => $country_name]) . '</span><br/>'
        . $this->t("We don't support delivery outside @country.", ['@country' => $country_name])
        . $this->t('Please select a store with in country @country below to continue.', ['@country' => $country_name]),
      ];

      $strings[] = [
        'key' => 'cnc_find_your_nearest_store',
        'value' => $this->t('find your nearest store'),
      ];

      $strings[] = [
        'key' => 'cnc_select_this_store',
        'value' => $this->t('select this store'),
      ];

      $strings[] = [
        'key' => 'cnc_collection_store',
        'value' => $this->t('Collection Store'),
      ];

      $strings[] = [
        'key' => 'cnc_no_store_found',
        'value' => $this->t('Sorry, No store found for your location.'),
      ];

      $strings[] = [
        'key' => 'cnc_collect_in_store',
        'value' => $this->t('Collect in store from'),
      ];

      $strings[] = [
        'key' => 'cnc_collection_details',
        'value' => $this->t('Collection details'),
      ];

      $strings[] = [
        'key' => 'cnc_selected_store',
        'value' => $this->t('Selected store'),
      ];

      $strings[] = [
        'key' => 'cnc_contact_info_subtitle',
        'value' => $this->t('We will send you a text message once your order is ready for collection.'),
      ];

      $strings[] = [
        'key' => 'cnc_list_view',
        'value' => $this->t('List view'),
      ];

      $strings[] = [
        'key' => 'cnc_map_view',
        'value' => $this->t('Map view'),
      ];

      $strings[] = [
        'key' => 'cnc_near_me',
        'value' => $this->t('Near me'),
      ];

      $strings[] = [
        'key' => 'cnc_distance',
        'value' => $this->t('@distance miles'),
      ];

    }

    $strings[] = [
      'key' => 'dismiss',
      'value' => $this->t('Dismiss'),
    ];

    $strings[] = [
      'key' => 'contact_information',
      'value' => $this->t('contact information'),
    ];

    $strings[] = [
      'key' => 'ci_full_name',
      'value' => $this->t('Full Name'),
    ];

    $strings[] = [
      'key' => 'ci_mobile_number',
      'value' => $this->t('Mobile Number'),
    ];

    $strings[] = [
      'key' => 'ci_email',
      'value' => $this->t('Email'),
    ];

    $strings[] = [
      'key' => 'location_access_denied',
      'value' => $this->t('Access to your location access has been denied by your browser. You can reenable location services in your browser settings.'),
    ];

    $strings[] = [
      'key' => 'form_error_full_name',
      'value' => $this->t('Please enter your full name.'),
    ];

    $strings[] = [
      'key' => 'form_error_email',
      'value' => $this->t('Please enter email.'),
    ];

    $strings[] = [
      'key' => 'form_error_mobile_number',
      'value' => $this->t('Please enter mobile number.'),
    ];

    $strings[] = [
      'key' => 'form_error_valid_mobile_number',
      'value' => $this->t('Please enter valid mobile number.'),
    ];

    $strings[] = [
      'key' => 'form_error_customer_exists',
      'value' => $this->t('Customer already exists.'),
    ];

    $strings[] = [
      'key' => 'form_error_email_not_valid',
      'value' => $this->t('The email address %mail is not valid.'),
    ];

    $strings[] = [
      'key' => 'hd_deliver_to_my_location',
      'value' => $this->t('deliver to my location'),
    ];

    $strings[] = [
      'key' => 'billing_select_my_location',
      'value' => $this->t('select my location'),
    ];

    $strings[] = [
      'key' => 'address_save',
      'value' => $this->t('save', [], ['context' => 'button']),
    ];

    $strings[] = [
      'key' => 'change_address',
      'value' => $this->t('change address'),
    ];

    $strings[] = [
      'key' => 'add_new_address',
      'value' => $this->t('add new address'),
    ];

    $strings[] = [
      'key' => 'map_enter_location',
      'value' => $this->t('Enter a location'),
    ];

    $strings[] = [
      'key' => 'address_search_for',
      'value' => $this->t('Search for @label'),
    ];

    $strings[] = [
      'key' => 'address_select',
      'value' => $this->t('Select @label'),
    ];

    $strings[] = [
      'key' => 'address_please_enter',
      'value' => $this->t('Please enter @label.'),
    ];

    $strings[] = [
      'key' => 'transaction_failed',
      'value' => $this->t('Transaction has been declined. Please try again later.'),
    ];

    $strings[] = [
      'key' => 'payment_error',
      'value' => $this->t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.'),
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
          'cnc_enabled' => $cnc_enabled,
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
      if (!($plugin->isAvailable())) {
        continue;
      }

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

    return $this->addCheckoutConfigSettings($build);
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
      'base_grand_total' => $order['totals']['grand'],
      'discount_amount' => $order['totals']['discount'],
      'free_delivery' => 'false',
      'surcharge' => $order['totals']['surcharge'],
    ];

    if ($orderDetails['delivery_type'] !== 'cc') {
      $totals['shipping_incl_tax'] = $order['totals']['shipping'] ?? 0;
    }

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
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $settings = [
      'site_details' => [
        'logo' => alshaya_master_get_email_logo(NULL, $langcode),
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

    $build = [
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
        'tags' => $cache_tags,
      ],
    ];

    return $this->addCheckoutConfigSettings($build);
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

  /**
   * Add required common configurations to settings.
   *
   * @param array $build
   *   Build array.
   *
   * @return array
   *   Build array updated with configurations in settings.
   */
  private function addCheckoutConfigSettings(array $build) {
    $settings = [];
    $cache_tags = [];

    $currency_config = $this->configFactory->get('acq_commerce.currency');
    $cache_tags = Cache::mergeTags($cache_tags, $currency_config->getCacheTags());

    $settings['alshaya_spc']['currency_config'] = [
      'currency_code' => $currency_config->get('currency_code'),
      'currency_code_position' => $currency_config->get('currency_code_position'),
      'decimal_points' => $currency_config->get('decimal_points'),
    ];

    $settings['alshaya_spc']['middleware_url'] = _alshaya_spc_get_middleware_url();

    $product_config = $this->configFactory->get('alshaya_acm_product.settings');
    $cache_tags = Cache::mergeTags($cache_tags, $currency_config->getCacheTags());

    // Time we get from configuration is in minutes.
    $settings['alshaya_spc']['productExpirationTime'] = $product_config->get('local_storage_cache_time') ?? 60;
    $settings['alshaya_spc']['vat_text'] = $product_config->get('vat_text');
    $settings['alshaya_spc']['vat_text_footer'] = $product_config->get('vat_text_footer');

    $build['#attached']['drupalSettings'] = array_merge_recursive($build['#attached']['drupalSettings'], $settings);
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'], $cache_tags);

    $build['#cache']['contexts'][] = 'languages:' . LanguageInterface::TYPE_INTERFACE;
    $build['#cache']['contexts'][] = 'user';

    return $build;
  }

}
