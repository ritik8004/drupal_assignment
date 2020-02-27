<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\Core\Language\LanguageInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    AlshayaSpcPaymentMethodManager $payment_method_manager,
    CheckoutOptionsManager $checkout_options_manager,
    MobileNumberUtilInterface $mobile_util,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->configFactory = $config_factory;
    $this->checkoutOptionManager = $checkout_options_manager;
    $this->paymentMethodManager = $payment_method_manager;
    $this->mobileUtil = $mobile_util;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager')
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
    $cc_config = $this->configFactory->get('alshaya_click_collect.settings');
    $checkout_settings = $this->configFactory->get('alshaya_acm_checkout.settings');

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

    $user_name = [];
    // If logged in user.
    if ($this->currentUser->isAuthenticated()) {
      $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
      $user_name = [
        'fname' => $user->get('field_first_name')->first()->getString(),
        'lname' => $user->get('field_last_name')->first()->getString(),
      ];
    }

    // Get country code.
    $country_code = _alshaya_custom_get_site_level_country_code();
    $store_finder_config = $this->configFactory->get('alshaya_stores_finder.settings');
    return [
      '#type' => 'markup',
      '#markup' => '<div id="spc-checkout"></div>',
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
          'cnc' => [
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
        ],
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
