<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\alshaya_i18n\AlshayaI18nLanguages;
use Drupal\alshaya_spc\Helper\AlshayaSpcOrderHelper;
use Drupal\alshaya_spc\Plugin\SpcPaymentMethod\CashOnDelivery;
use Drupal\alshaya_spc\Plugin\SpcPaymentMethod\CheckoutComUpapiFawry;
use Drupal\alshaya_user\AlshayaUserInfo;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Site\Settings;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\alshaya_addressbook\AddressBookAreasTermsHelper;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper;
use Drupal\alshaya_acm_product\DeliveryOptionsHelper;

/**
 * Class Alshaya Spc Controller.
 */
class AlshayaSpcController extends ControllerBase {

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
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Checkout.com API Helper.
   *
   * @var \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper
   */
  protected $checkoutComApiHelper;

  /**
   * Delivery Options helper.
   *
   * @var \Drupal\alshaya_acm_product\DeliveryOptionsHelper
   */
  protected $deliveryOptionsHelper;

  /**
   * Date time formatter interface.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * AlshayaSpcController constructor.
   *
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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   * @param \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper $checkout_com_api_helper
   *   Acm checkout com api helper.
   * @param \Drupal\alshaya_acm_product\DeliveryOptionsHelper $delivery_options_helper
   *   Delivery Options Helper.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(AlshayaSpcPaymentMethodManager $payment_method_manager,
                              CheckoutOptionsManager $checkout_options_manager,
                              MobileNumberUtilInterface $mobile_util,
                              AccountProxyInterface $current_user,
                              EntityTypeManagerInterface $entity_type_manager,
                              AddressBookAreasTermsHelper $areas_term_helper,
                              AlshayaSpcOrderHelper $order_helper,
                              LanguageManagerInterface $language_manager,
                              ModuleHandlerInterface $module_handler,
                              AlshayaAcmCheckoutComAPIHelper $checkout_com_api_helper,
                              DeliveryOptionsHelper $delivery_options_helper,
                              DateFormatterInterface $date_formatter) {
    $this->checkoutOptionManager = $checkout_options_manager;
    $this->paymentMethodManager = $payment_method_manager;
    $this->mobileUtil = $mobile_util;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->areaTermsHelper = $areas_term_helper;
    $this->orderHelper = $order_helper;
    $this->languageManager = $language_manager;
    $this->moduleHandler = $module_handler;
    $this->checkoutComApiHelper = $checkout_com_api_helper;
    $this->deliveryOptionsHelper = $delivery_options_helper;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.alshaya_spc_payment_method'),
      $container->get('alshaya_acm_checkout.options_manager'),
      $container->get('mobile_number.util'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_addressbook.area_terms_helper'),
      $container->get('alshaya_spc.order_helper'),
      $container->get('language_manager'),
      $container->get('module_handler'),
      $container->get('alshaya_acm_checkoutcom.api_helper'),
      $container->get('alshaya_acm_product.delivery_options_helper'),
      $container->get('date.formatter')
    );
  }

  /**
   * Overridden controller for cart page.
   *
   * @return array
   *   Markup for cart page.
   */
  public function cart() {
    $acm_config = $this->config('alshaya_acm.settings');
    $cache_tags = $acm_config->getCacheTags();

    $cart_config = $this->config('alshaya_acm.cart_config');
    $cache_tags = Cache::mergeTags($cache_tags, $cart_config->getCacheTags());

    $acm_checkout_settings = $this->config('alshaya_acm_checkout.settings');
    $cache_tags = Cache::mergeTags($cache_tags, $acm_checkout_settings->getCacheTags());

    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // Get country code.
    $country_code = _alshaya_custom_get_site_level_country_code();
    $store_finder_settings = $this->config('alshaya_stores_finder.settings');
    $cache_tags = Cache::mergeTags($cache_tags, $store_finder_settings->getCacheTags());

    $build = [
      '#type' => 'markup',
      '#markup' => '<div id="spc-cart"></div>',
      '#attached' => [
        'library' => [
          'alshaya_spc/cart',
          'alshaya_spc/cart-sticky-header',
          'alshaya_white_label/spc-cart',
          'alshaya_acm_promotion/basket_labels_manager',
          'alshaya_white_label/free_gifts',
        ],
        'drupalSettings' => [
          'item_code_label' => $this->t('Item code'),
          'quantity_limit_enabled' => $acm_config->get('quantity_limit_enabled'),
          'country_code' => $country_code,
          'country_mobile_code' => $this->mobileUtil->getCountryCode($country_code),
          'mobile_maxlength' => $this->config('alshaya_master.mobile_number_settings')->get('maxlength'),
          'hide_max_qty_limit_message' => $acm_config->get('hide_max_qty_limit_message'),
          'address_fields' => _alshaya_spc_get_address_fields(),
          'alshaya_spc' => [
            'max_cart_qty' => $cart_config->get('max_cart_qty'),
            'cart_storage_expiration' => $cart_config->get('cart_storage_expiration') ?? 15,
            'display_cart_crosssell' => $cart_config->get('display_cart_crosssell') ?? TRUE,
            'display_cart_payment_icons' => $this->config('alshaya_spc.settings')->get('display_cart_payment_icons') ?? FALSE,
            'lng' => AlshayaI18nLanguages::getLocale($langcode),
          ],
          // This key gets the dynamic area value of the area placeholder
          // and will be used in SDD/ED delievry area panel on Cart page.
          'areaBlockFormPlaceholder' => $store_finder_settings->get('store_search_placeholder'),
        ],
      ],
      '#cache' => [
        'tags' => $cache_tags,
      ],
    ];

    // Get payment methods and attach to Drupal settings.
    $this->addPaymentMethodsToBuild($build);

    $build = $this->addCheckoutConfigSettings($build);

    $checkout_settings = Settings::get('alshaya_checkout_settings');
    $build['#attached']['drupalSettings']['cart']['refreshMode'] = $checkout_settings['cart_refresh_mode'];

    // Advantage card settings only available if it is enabled.
    $advantage_card_config = $this->config('alshaya_spc.advantage_card');
    if ($advantage_card_config->get('advantageCardEnabled')) {
      $build['#attached']['drupalSettings']['alshaya_spc']['advantageCard'] = [
        'enabled' => $advantage_card_config->get('advantageCardEnabled'),
        'advantageCardPrefix'  => $advantage_card_config->get('advantageCardPrefix'),
      ];
    }
    $build['#cache']['tags'] = Cache::mergeTags($cache_tags, $advantage_card_config->getCacheTags());

    $express_delivery_config = $this->config('alshaya_spc.express_delivery');
    // Show express delivery options if feature is enabled.
    if ($this->deliveryOptionsHelper->ifSddEdFeatureEnabled()) {
      $build['#attached']['drupalSettings']['expressDelivery'] = [
        'enabled' => TRUE,
      ];
      $build['#attached']['library'][] = 'alshaya_white_label/sameday-express-delivery';
    }
    $build['#cache']['tags'] = Cache::mergeTags($cache_tags, $express_delivery_config->getCacheTags());

    // Add collection point feature config variables.
    $collection_points_config = $this->config('alshaya_spc.collection_points');
    $build['#attached']['drupalSettings']['cnc_collection_points_enabled'] = $collection_points_config->get('click_collect_collection_points_enabled');

    $build['#cache']['tags'] = Cache::mergeTags($cache_tags, array_merge(
      $advantage_card_config->getCacheTags(),
      $collection_points_config->getCacheTags(),
    ));
    $this->moduleHandler->alter('alshaya_spc_cart_build', $build);

    return $build;
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

    $cc_config = $this->config('alshaya_click_collect.settings');
    $cache_tags = Cache::mergeTags($cache_tags, $cc_config->getCacheTags());

    $checkout_settings = $this->config('alshaya_acm_checkout.settings');
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
        'fname' => AlshayaUserInfo::getUserNameField($user, 'field_first_name'),
        'lname' => AlshayaUserInfo::getUserNameField($user, 'field_last_name'),
        'mobile' => !empty($user_mobile_number) ? $user_mobile_number->getValue()['local_number'] : '',
      ];

      $default_profile = $this->entityTypeManager->getStorage('profile')
        ->loadDefaultByUser($user, 'address_book');
      if ($default_profile) {
        $user_name['address_available'] = TRUE;
      }
    }

    $areas = [];
    $areas_translations = $this->areaTermsHelper->getAllAreas(TRUE, TRUE);
    $governates_translations = $this->areaTermsHelper->getAllGovernates(TRUE, TRUE);
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

    $spc_cnc_config = $this->config('alshaya_spc.click_n_collect');
    $store_finder_config = $this->config('alshaya_stores_finder.settings');
    $geolocation_config = $this->config('geolocation_google_maps.settings');
    $collection_points_config = $this->config('alshaya_spc.collection_points');

    $cache_tags = Cache::mergeTags(
      $cache_tags,
      array_merge(
        $spc_cnc_config->getCacheTags(),
        $store_finder_config->getCacheTags(),
        $geolocation_config->getCacheTags(),
        $collection_points_config->getCacheTags(),
      )
    );

    $country_name = $this->mobileUtil->getCountryName($country_code);
    $strings[] = [
      'key' => 'location_outside_country_hd',
      'value' => '<span class="font-bold">' . $this->t('You are browsing outside @country', ['@country' => $country_name]) . '</span><br/>'
      . $this->t("We don't support delivery outside @country.", ['@country' => $country_name])
      . $this->t('Please enter an address with in country @country below to continue.', ['@country' => $country_name]),
    ];

    $strings[] = [
      'key' => 'address_not_complete',
      'value' => $this->t("This address doesn't contain all the required information, please update it."),
    ];

    $strings[] = [
      'key' => 'address_not_filled',
      'value' => $this->t("Sorry, we couldn’t fetch your location precisely, please") . '<span class="font-bold" id="scroll-to-dropdown"> ' . $this->t("enter your address") . '</span>',
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

    $login_link = Link::createFromRoute(
      $this->t('please login'),
      'alshaya_spc.checkout.login',
      [],
      ['attributes' => ['id' => 'spc-checkout-customer-login-link']]
    );

    $strings[] = [
      'key' => 'form_error_customer_exists',
      'value' => [
        '#markup' => (string) $this->t('You already have an account, @login_link.', [
          '@login_link' => $login_link->toString()->getGeneratedLink(),
        ]),
      ],
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

    $strings[] = [
      'key' => 'place_order_failed_error',
      'value' => $this->t('Sorry, the transaction has been successful but your order is still being processed. If you do not receive an order confirmation in next 6 hours please contact our customer service at live chat and quote the following information:<br>@transaction_data'),
    ];

    $strings[] = [
      'key' => 'shipping_method_error',
      'value' => $this->t('Delivery Information is incomplete. Please update and try again.'),
    ];

    $strings[] = [
      'key' => 'delivery_area_question',
      'value' => $this->t('Do you want to change your current Delivery Area from @currentAreaLabel to @storageAreaLabel?', [], ['context' => 'delivery_area']),
    ];

    // Adding translation strings for the online booking start and end time.
    $strings[] = [
      'key' => 'online_booking_am',
      'value' => $this->t('AM', [], ['context' => 'online_booking']),
    ];
    $strings[] = [
      'key' => 'online_booking_pm',
      'value' => $this->t('PM', [], ['context' => 'online_booking']),
    ];

    $build = [
      '#theme' => 'spc_checkout',
      '#areas' => $areas,
      '#strings' => $strings,
      '#attached' => [
        'library' => [
          'alshaya_acm_checkout/ab_testing',
          'alshaya_spc/googlemapapi',
          'alshaya_spc/commerce_backend.checkout',
          'alshaya_spc/checkout',
          'alshaya_white_label/spc-checkout',
        ],
        'drupalSettings' => [
          'areas_translations' => $areas_translations,
          'governates_translations' => $governates_translations,
          'cnc_enabled' => $cnc_enabled,
          'cnc_subtitle_available' => $cc_config->get('checkout_click_collect_available'),
          'cnc_subtitle_unavailable' => $cc_config->get('checkout_click_collect_unavailable'),
          'terms_condition' => $checkout_settings->get('checkout_terms_condition.value'),
          'address_fields' => _alshaya_spc_get_address_fields(),
          'country_code' => $country_code,
          'country_mobile_code' => $this->mobileUtil->getCountryCode($country_code),
          'user_name' => $user_name,
          'mobile_maxlength' => $this->config('alshaya_master.mobile_number_settings')->get('maxlength'),
          'google_field_mapping' => $this->config('alshaya_spc.google_mapping')->get('mapping'),
          'map' => [
            'google_api_key' => $geolocation_config->get('google_map_api_key'),
            'center' => $spc_cnc_config->get('country_center'),
            'placeholder' => $store_finder_config->get('store_search_placeholder'),
            'map_marker' => $store_finder_config->get('map_marker'),
            'cnc_shipping' => [
              'code' => $cncTerm->get('field_shipping_carrier_code')->getString(),
              'method' => $cncTerm->get('field_shipping_method_code')->getString(),
            ],
          ],
          'cnc_stores_limit' => $spc_cnc_config->get('cnc_stores_limit'),
          'cncStoreInfoCacheTime' => $checkout_settings->get('cnc_store_info_cache_time'),
          'cnc_collection_points_enabled' => $collection_points_config->get('click_collect_collection_points_enabled'),
        ],
      ],
      '#cache' => [
        'tags' => $cache_tags,
      ],
    ];

    // Get payment methods and attach to Drupal settings.
    $this->addPaymentMethodsToBuild($build);
    // Process build data for each payment method available on checkout page. It
    // will add processed data in drupal settings and will attach the necessary
    // libraries to the checkout build.
    foreach ($build['#attached']['drupalSettings']['payment_methods'] ?? [] as $payment_method) {
      /** @var \Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase $plugin */
      $plugin = $this->paymentMethodManager->createInstance($payment_method['code']);
      // Check if payment method is available at backend else remove it from the
      // options to checkout.
      if (!($plugin->isAvailable())) {
        unset($build['#attached']['drupalSettings']['payment_methods'][$payment_method['code']]);
        continue;
      }

      // If available proceed to process the required build.
      $plugin->processBuild($build);
    }

    $build = $this->addCheckoutConfigSettings($build);

    $checkout_settings = Settings::get('alshaya_checkout_settings');
    $build['#attached']['drupalSettings']['cart']['siteInfo'] = alshaya_get_site_country_code();
    $build['#attached']['drupalSettings']['cart']['addressFields'] = Settings::get('alshaya_address_fields', []);

    $express_delivery_config = $this->config('alshaya_spc.express_delivery');

    // Show express delivery options if feature is enabled.
    if ($this->deliveryOptionsHelper->ifSddEdFeatureEnabled()) {
      $build['#attached']['drupalSettings']['expressDelivery'] = [
        'enabled' => TRUE,
      ];
      $build['#attached']['library'][] = 'alshaya_white_label/sameday-express-delivery';
    }
    $build['#cache']['tags'] = Cache::mergeTags($cache_tags, $express_delivery_config->getCacheTags());

    $this->moduleHandler->alter('alshaya_spc_checkout_build', $build);
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
    $delivery_method_description = $orderDetails['delivery_method_description'] ?? '';
    // Display custom label in description
    // if same day delivery is selected as shipping method.
    $checkout_settings = $this->config('alshaya_acm_checkout.settings');

    // Get formatted customer phone number.
    $phone_number = '';
    if (in_array('address', array_keys($order['shipping']))) {
      $phone_number = $this->orderHelper->getFormattedMobileNumber($order['shipping']['address']['telephone'] ?? '');
    }

    // Order Totals.
    $totals = [
      'subtotal_incl_tax' => $order['totals']['sub'],
      'base_grand_total' => $order['totals']['grand'],
      'discount_amount' => $order['totals']['discount'],
      'free_delivery' => 'false',
      'surcharge' => $order['totals']['surcharge'],
    ];

    if (isset($orderDetails['delivery_type']) && $orderDetails['delivery_type'] !== 'cc') {
      $totals['shipping_incl_tax'] = $order['totals']['shipping'] ?? 0;
    }

    // Advantage card related config for order confirmation page.
    $advantage_card_config = $this->config('alshaya_spc.advantage_card');
    if ($advantage_card_config->get('advantageCardEnabled') && $order['coupon'] === 'advantage_card') {
      $totals['advatage_card'] = TRUE;
    }
    // Get Products.
    $productList = [];
    $number_of_items = 0;
    foreach ($order['items'] as $item) {
      if (in_array($item['sku'], array_keys($productList))) {
        continue;
      }
      // For Egift card set itemKey as item_id.
      $itemKey = $item['is_virtual'] ? $item['item_id'] : $item['sku'];
      // Populate price and other info from order response data.
      $productList[$itemKey] = $this->orderHelper->getSkuDetails($item);
      // Calculate the ordered quantity of each sku.
      $number_of_items += $productList[$itemKey]['qtyOrdered'];
    }

    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $collection_settings = $this->config('alshaya_spc.collection_points');
    $cnc_collection_points_enabled = $collection_settings->get('click_collect_collection_points_enabled');

    $settings = [
      'site_details' => [
        'logo' => alshaya_master_get_email_logo(NULL, $langcode),
        'sub_brand_logo' => _alshaya_master_get_subbrand_logo_image_link(),
        'customer_service_text' => $checkout_settings->get('checkout_customer_service'),
      ],
      'order_details' => [
        'customer_email' => $order['email'],
        'order_number' => $order['increment_id'],
        'customer_name' => $order['firstname'] . ' ' . $order['lastname'],
        'mobile_number' => $phone_number,
        'expected_delivery' => $delivery_method_description,
        'number_of_items' => $number_of_items,
        'delivery_type_info' => $orderDetails,
        'totals' => $totals,
        'items' => $productList,
        'payment' => $orderDetails['payment'],
        'billing' => $orderDetails['billing'],
      ],
      'cnc_collection_points_enabled' => $cnc_collection_points_enabled ?? FALSE,
    ];

    // Check if we are getting Inter country transfer details
    // along with order details,
    // if yes, we are assigning this to drupalSettings.
    if (isset($order['extension'])
      && !empty($order['extension']['oms_lead_time'])) {
      $settings['order_details']['ict_date'] = $this->dateFormatter->format(
        strtotime($order['extension']['oms_lead_time']),
        'ict',
        'dS M Y',
      );
    }

    if ($orderDetails['payment']['methodCode'] === 'cashondelivery') {
      $strings = array_merge($strings, CashOnDelivery::getCodSurchargeStrings());
    }

    // Get static text for Fawry payment.
    if ($orderDetails['payment']['methodCode'] === 'checkout_com_upapi_fawry') {
      $strings = array_merge($strings, CheckoutComUpapiFawry::getFawryStaticText());
    }

    if ($orderDetails['payment']['methodCode'] === 'checkout_com_upapi_benefitpay') {
      $checkoutcomConfig = $this->checkoutComApiHelper->getCheckoutcomUpApiConfig();
      $settings['order_details']['payment']['environment'] = $checkoutcomConfig['environment'];
      $settings['order_details']['payment']['benefitpayMerchantId'] = $checkoutcomConfig['benefit_pay_merchant_id'];
      $settings['order_details']['payment']['benefitpayAppId'] = $checkoutcomConfig['benefit_pay_app_id'];
      $settings['order_details']['payment']['benefitpaySecretKey'] = $checkoutcomConfig['benefit_pay_secret_key'];
    }

    // Add online order booking information in drupal settings if available.
    if (isset($orderDetails['online_booking_information'])) {
      $settings['order_details']['onlineBookingInformation'] = $orderDetails['online_booking_information'];
    }

    $cache_tags = [];
    $cache_tags = Cache::mergeTags($cache_tags, array_merge(
      $checkout_settings->getCacheTags(),
      $collection_settings->getCacheTags(),
    ));

    // If logged in user.
    if ($this->currentUser->isAuthenticated()) {
      $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
      $cache_tags = Cache::mergeTags($cache_tags, $user->getCacheTags());
    }

    // Invoke the alter hook to allow other modules to change
    // the order detail settings.
    $this->moduleHandler->alter('alshaya_spc_order_details_settings', $settings, $order);

    $build = [
      '#theme' => 'spc_confirmation',
      '#strings' => $strings,
      '#attached' => [
        'library' => [
          'alshaya_spc/commerce_backend.checkout',
          'alshaya_spc/checkout-confirmation',
          'alshaya_white_label/spc-checkout-confirmation',
        ],
        'drupalSettings' => $settings,
      ],
      '#cache' => [
        'tags' => $cache_tags,
      ],
    ];

    $build = $this->addCheckoutConfigSettings($build);
    // Added Olapic checkout pixel condition.
    // Check first if alshaya olapic module is enabled.
    if ($this->moduleHandler->moduleExists('alshaya_olapic')) {
      $data_apikey_field_name = 'olapic_' . $langcode . '_data_apikey';
      $data_apikey = $this->configFactory->get('alshaya_olapic.settings')->get($data_apikey_field_name) ?? '';
      $base_currency_code = $order['base_currency_code'] ?? '';
      if (!empty($data_apikey)) {
        $this->moduleHandler->alter('checkout_pixel_build', $build, $data_apikey, $base_currency_code);
      }
    }

    if ($cnc_collection_points_enabled) {
      $build['#attached']['library'][] = 'alshaya_white_label/checkout-confirmation-pudo-aramex';
    }
    // Adding hook alter for bazaarvoice pixel integration.
    $this->moduleHandler->alter('alshaya_spc_checkout_confirmation_order_build', $build, $order);
    return $build;
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
        case 'fullname':
          // If full name is not empty.
          if (!empty($value) && !empty(trim($value['firstname'])) && !empty(trim($value['lastname']))) {
            $status[$key] = TRUE;
          }
          break;

        case 'mobile':
          $country_code = _alshaya_custom_get_site_level_country_code();
          $country_mobile_code = '+' . $this->mobileUtil->getCountryCode($country_code);

          if (!empty($data['chosenCountryCode'])) {
            $country_mobile_code = '+' . $data['chosenCountryCode'];
          }

          $raw_number = $value;
          if (!str_contains($value, $country_mobile_code)) {
            $value = $country_mobile_code . $value;
          }

          try {
            // Remove country code from raw number if added so that
            // validation can be done only on raw number.
            $raw_number = str_replace($country_mobile_code, '', $raw_number);
            // If mobile number not contains only digits.
            if (!preg_match('/^[0-9]+$/', $raw_number)) {
              throw new \Exception('Invalid mobile number.');
            }

            if ($this->mobileUtil->testMobileNumber($value)) {
              $status[$key] = TRUE;
            }
          }
          catch (\Exception) {
            $status[$key] = FALSE;
          }

          break;

        case 'email':
          // For email validation we do two checks:
          // 1. email domain is valid
          // 2. email is not of an existing customer.
          $domain = explode('@', $value)[1];
          $host = gethostbyname($domain);
          if (empty($host) || $host === $domain) {
            $status[$key] = 'invalid';
          }
          else {
            if ($key == 'email') {
              $user = user_load_by_mail($value);
              $status[$key] = ($user instanceof UserInterface) ? 'exists' : '';
            }
          }
          break;

        case 'address':
          $status[$key] = TRUE;
          $address_extension_attributes = $data[$key]['extension_attributes'] ?? [];
          $address_custom_attributes = $data[$key]['custom_attributes'] ?? [];
          // @todo Check AlshayaAddressBookManager::validateAddress().
          // We are using '::validateAddress()' for addressbook validation.
          // We need to check if we can use same for checkout as well.
          // Currenlty we are not doing this because '::validateAddress()'
          // doesn't do any validation for area/city field which is the
          // actual requirement here.
          // Iterate over each configured address field.
          $address_keys_filled = [];
          $spc_address_fields = _alshaya_spc_get_address_fields();
          foreach ($spc_address_fields as $field => $address_field) {
            // If field is available and is either area/city.
            $val_to_validate = NULL;
            // FLag to determine if city/area field value filled.
            $city_area_field = FALSE;
            if (!empty($address_extension_attributes) && isset($address_extension_attributes[$address_field['key']])
              && ($field == 'administrative_area' || $field == 'area_parent')) {
              $val_to_validate = $address_extension_attributes[$address_field['key']];
              $city_area_field = TRUE;
              $address_keys_filled[$field] = $val_to_validate;
            }
            elseif (!empty($address_custom_attributes)) {
              foreach ($address_custom_attributes as $attr) {
                if ($attr['attribute_code'] == $address_field['key']
                  && ($field == 'administrative_area' || $field == 'area_parent')) {
                  $val_to_validate = $attr['value'];
                  $city_area_field = TRUE;
                  $address_keys_filled[$field] = $val_to_validate;
                  break;
                }
              }
            }

            try {
              if ($city_area_field) {
                $term = $this->areaTermsHelper->getLocationTermFromLocationId($val_to_validate);
                if (!$term) {
                  $status[$key] = FALSE;
                  break;
                }
              }
            }
            catch (\Exception) {
              $status[$key] = FALSE;
              break;
            }
          }

          // We check here only when all validations are passed above.
          if ($status[$key]) {
            // If address structure has area_parent / administrative_area but
            // address don't contains both of them.
            if (isset($spc_address_fields['area_parent']) && empty($address_keys_filled['area_parent'])) {
              $status[$key] = FALSE;
            }
            elseif (isset($spc_address_fields['administrative_area']) && empty($address_keys_filled['administrative_area'])) {
              $status[$key] = FALSE;
            }
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

    $currency_config = $this->config('acq_commerce.currency');
    $cache_tags = Cache::mergeTags($cache_tags, $currency_config->getCacheTags());

    $settings['alshaya_spc']['currency_config'] = [
      'currency_code' => $currency_config->get('currency_code'),
      'currency_code_position' => $currency_config->get('currency_code_position'),
      'decimal_points' => $currency_config->get('decimal_points'),
    ];

    $product_config = $this->config('alshaya_acm_product.settings');
    $cache_tags = Cache::mergeTags($cache_tags, $product_config->getCacheTags());

    // Flags text.
    $settings['alshaya_spc']['non_refundable_tooltip'] = $product_config->get('non_refundable_tooltip');
    $settings['alshaya_spc']['non_refundable_text'] = $product_config->get('non_refundable_text');
    $settings['alshaya_spc']['delivery_in_only_city_text'] = $product_config->get('delivery_in_only_city_text');
    $settings['alshaya_spc']['delivery_in_only_city_key'] = (int) $product_config->get('delivery_in_only_city_key');

    // Time we get from configuration is in minutes.
    $settings['alshaya_spc']['vat_text'] = $product_config->get('vat_text');
    $settings['alshaya_spc']['vat_text_footer'] = $product_config->get('vat_text_footer');

    // Advantage crad related config for Checkout.
    $advantage_card_config = $this->config('alshaya_spc.advantage_card');
    if ($advantage_card_config->get('advantageCardEnabled')) {
      $settings['alshaya_spc']['advantageCard'] = [
        'enabled' => $advantage_card_config->get('advantageCardEnabled'),
        'advantageCardPrefix'  => $advantage_card_config->get('advantageCardPrefix'),
      ];
    }

    // Subtotal after discount related config for Cart/Checkout.
    $alshaya_spc_config = $this->config('alshaya_spc.settings');
    $cache_tags = Cache::mergeTags($cache_tags, $alshaya_spc_config->getCacheTags());

    $settings['alshaya_spc']['subtotal_after_discount'] = $alshaya_spc_config->get('subtotal_after_discount');

    // Show size alternates on cart and checkout.
    $product_display_settings = $this->config('alshaya_acm_product.display_settings');
    $settings['alshaya_spc']['sizeGroupAttribute'] = $product_display_settings->get('size_group.pdp');
    $settings['alshaya_spc']['sizeGroupAlternates'] = array_reduce($product_display_settings->get('size_group.alternates'), function ($unique_arr, $currentValue) {
      if (!in_array($currentValue['value'], array_column($unique_arr, 'value'))) {
        $unique_arr[$currentValue['value']] = $currentValue['label'];
      }
      return $unique_arr;
    }, []);

    $build['#attached']['drupalSettings'] = array_merge_recursive($build['#attached']['drupalSettings'], $settings);

    // Get shipping methods and attach to Drupal settings.
    $shipping_methods = [];
    $shipping_options = $this->checkoutOptionManager->getAllShippingTerms() ?? [];
    foreach ($shipping_options as $shipping_option) {
      $method_code = $shipping_option->get('field_shipping_method_code')->getValue();
      if (!empty($method_code)) {
        $shipping_methods[$method_code[0]['value']] = $shipping_option->label();

        if ($this->languageManager->getCurrentLanguage()->getId() !== 'en') {
          $shipping_option_en = $shipping_option->getTranslation('en');
          $shipping_methods[$method_code[0]['value']] = $shipping_option_en->label();
        }
      }
    }
    $build['#attached']['drupalSettings']['shipping_methods_translations'] = $shipping_methods;

    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'], $cache_tags);

    $build['#cache']['contexts'][] = 'languages:' . LanguageInterface::TYPE_INTERFACE;
    $build['#cache']['contexts'][] = 'user';

    return $build;
  }

  /**
   * Add payment methods and configurations to settings.
   *
   * @param array $build
   *   Build array.
   */
  private function addPaymentMethodsToBuild(array &$build) {
    $checkout_settings = $this->config('alshaya_acm_checkout.settings');

    $ab_testing = $this->config('alshaya_acm_checkout.ab_testing');
    $cache_tags = Cache::mergeTags($build['#cache']['tags'] ?? [], $ab_testing->getCacheTags());

    // Get payment methods.
    $payment_methods = [];
    $exclude_payment_methods = array_filter($checkout_settings->get('exclude_payment_methods'));

    foreach ($this->paymentMethodManager->getDefinitions() ?? [] as $payment_method) {
      // Avoid displaying the excluded methods.
      if (isset($exclude_payment_methods[$payment_method['id']])) {
        continue;
      }

      // Get the payment method term data.
      $payment_method_term = $this->checkoutOptionManager->loadPaymentMethod($payment_method['id'], $payment_method['label']->__toString());

      $payment_methods[$payment_method['id']] = [
        'name' => $payment_method_term->label(),
        'gtm_name' => $payment_method_term->label(),
        'description' => $payment_method_term->getDescription(),
        'code' => $payment_method_term->get('field_payment_code')->getString(),
        'default' => ($payment_method_term->get('field_payment_default')->getString() == '1'),
        'weight' => $payment_method_term->getWeight(),
        'ab_testing' => $ab_testing->get($payment_method['id']) ?? FALSE,
      ];

      if ($this->languageManager->getCurrentLanguage()->getId() !== 'en') {
        $payment_method_term_en = $payment_method_term->getTranslation('en');
        $payment_methods[$payment_method['id']]['gtm_name'] = $payment_method_term_en->label();
      }

      // Show default on top.
      $payment_methods[$payment_method['id']]['weight'] = $payment_methods[$payment_method['id']]['default']
        ? -999
        : (int) $payment_method_term->getWeight();
    }
    $arrayColumn = array_column($payment_methods, 'weight');

    array_multisort($arrayColumn, SORT_ASC, $payment_methods);
    $build['#attached']['drupalSettings']['payment_methods'] = $payment_methods;
    $build['#cache']['tags'] = $cache_tags;
  }

}
