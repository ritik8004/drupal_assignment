<?php

namespace Drupal\alshaya_spc\Controller;

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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    AlshayaSpcPaymentMethodManager $payment_method_manager,
    CheckoutOptionsManager $checkout_options_manager,
    MobileNumberUtilInterface $mobile_util,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    AddressBookAreasTermsHelper $areas_term_helper
  ) {
    $this->configFactory = $config_factory;
    $this->checkoutOptionManager = $checkout_options_manager;
    $this->paymentMethodManager = $payment_method_manager;
    $this->mobileUtil = $mobile_util;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->areaTermsHelper = $areas_term_helper;
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
      $container->get('alshaya_addressbook.area_terms_helper')
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

    $cc_config = $this->configFactory->get('alshaya_click_collect.settings');
    $cache_tags = Cache::mergeTags($cache_tags, $cc_config->getCacheTags());

    $checkout_settings = $this->configFactory->get('alshaya_acm_checkout.settings');
    $cache_tags = Cache::mergeTags($cache_tags, $checkout_settings->getCacheTags());

    // Get payment methods.
    $payment_methods = [];
    foreach ($this->paymentMethodManager->getDefinitions() ?? [] as $payment_method) {
      $payment_method_term = $this->checkoutOptionManager->loadPaymentMethod(
        $payment_method['id'],
        $payment_method['label']->render()
      );
      $payment_methods[$payment_method['id']] = [
        'name' => $payment_method_term->getName(),
        'description' => $payment_method_term->getDescription(),
        'code' => $payment_method_term->get('field_payment_code')->getString(),
        'default' => ($payment_method_term->get('field_payment_default')->getString() == '1'),
        'has_form' => $payment_method['hasForm'],
      ];
    }

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
    $cache_tags = Cache::mergeTags($cache_tags, $store_finder_config->getCacheTags());

    return [
      '#theme' => 'spc_checkout',
      '#areas' => $areas,
      '#attached' => [
        'library' => [
          'alshaya_spc/checkout',
          'alshaya_spc/spc.google_map',
          'alshaya_white_label/spc-checkout',
        ],
        'drupalSettings' => [
          'cnc_subtitle_available' => $cc_config->get('checkout_click_collect_available'),
          'cnc_subtitle_unavailable' => $cc_config->get('checkout_click_collect_unavailable'),
          'terms_condition' => $checkout_settings->get('checkout_terms_condition.value'),
          'payment_methods' => $payment_methods,
          'address_fields' => _alshaya_spc_get_address_fields(),
          'country_code' => $country_code,
          'country_mobile_code' => $this->mobileUtil->getCountryCode($country_code),
          'user_name' => $user_name,
          'mobile_maxlength' => $this->config('alshaya_master.mobile_number_settings')->get('maxlength'),
          'map' => [
            'center' => $store_finder_config->get('country_center'),
            'placeholder' => $store_finder_config->get('store_search_placeholder'),
            'map_marker' => [
              'icon' => $store_finder_config->get('marker.url'),
              'label_position' => $store_finder_config->get('marker.label_position'),
            ],
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
  }

  /**
   * Checkout Confirmation page.
   *
   * @return array
   *   Markup for checkout confirmation page.
   */
  public function checkoutConfirmation() {
    // @todo: Pull order details from MDC for the recent order.
    return [
      '#type' => 'markup',
      '#markup' => '<div id="spc-checkout-confirmation"></div>',
      '#attached' => [
        'library' => [
          'alshaya_spc/checkout-confirmation',
          'alshaya_white_label/spc-checkout-confirmation',
        ],
      ],
    ];
  }

  /**
   * Verifies the mobile number.
   *
   * @param string $mobile
   *   Mobile number to verify.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function verifyMobileNumber(string $mobile) {
    if (empty($mobile)) {
      return new JsonResponse(['status' => FALSE]);
    }

    try {
      $country_code = _alshaya_custom_get_site_level_country_code();
      $country_mobile_code = $this->mobileUtil->getCountryCode($country_code);
      if ($this->mobileUtil->testMobileNumber('+' . $country_mobile_code . $mobile)) {
        return new JsonResponse(['status' => TRUE]);
      }
    }
    catch (\Exception $e) {
      return new JsonResponse(['status' => FALSE]);
    }

    return new JsonResponse(['status' => FALSE]);
  }

}
