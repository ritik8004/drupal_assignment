<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\alshaya_click_collect\Service\AlshayaClickCollect;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager;
use Drupal\alshaya_spc\Helper\AlshayaSpcHelper;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
   * SPC helper.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcHelper
   */
  protected $spcHelper;

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
   * Alshaya click and collect helper.
   *
   * @var \Drupal\alshaya_click_collect\Service\AlshayaClickCollect
   */
  protected $clickCollect;

  /**
   * AlshayaSpcController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager $payment_method_manager
   *   Payment method manager.
   * @param \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager
   *   Checkout option manager.
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcHelper $spc_helper
   *   SPC helper.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\alshaya_click_collect\Service\AlshayaClickCollect $clickCollect
   *   Alshaya click and collect helper.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    AlshayaSpcPaymentMethodManager $payment_method_manager,
    CheckoutOptionsManager $checkout_options_manager,
    AlshayaSpcHelper $spc_helper,
    MobileNumberUtilInterface $mobile_util,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaClickCollect $clickCollect
  ) {
    $this->configFactory = $config_factory;
    $this->checkoutOptionManager = $checkout_options_manager;
    $this->paymentMethodManager = $payment_method_manager;
    $this->spcHelper = $spc_helper;
    $this->mobileUtil = $mobile_util;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->clickCollect = $clickCollect;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.alshaya_spc_payment_method'),
      $container->get('alshaya_acm_checkout.options_manager'),
      $container->get('alshaya_spc.helper'),
      $container->get('mobile_number.util'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_click_collect.helper')
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

    // Get country code.
    $country_code = _alshaya_custom_get_site_level_country_code();

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
          'address_fields' => $this->spcHelper->getAddressFields(),
          'country_code' => $country_code,
          'country_mobile_code' => $this->mobileUtil->getCountryCode($country_code),
          'map_marker_icon' => $this->configFactory->get('alshaya_stores_finder.settings')->get('marker.url'),
          'mobile_maxlength' => $this->config('alshaya_master.mobile_number_settings')->get('maxlength'),
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

  /**
   * Get area list for a given parent area.
   *
   * @param mixed $area
   *   Parent area id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getAreaListByParent($area) {
    $data = $this->spcHelper->getAllAreasOfParent($area);
    return new JsonResponse($data);
  }

  /**
   * Get areas list.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getAreaList() {
    $data = $this->spcHelper->getAreaList();
    return new JsonResponse($data);
  }

  /**
   * Get parent areas list.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getParentAreaList() {
    $data = $this->spcHelper->getAreaParentList();
    return new JsonResponse($data);
  }

  /**
   * Get all address list of the current user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getUserAddressList() {
    $uid = $this->currentUser->getAccount()->id();
    $addressList = $this->spcHelper->getAddressListByUid($uid);

    return new JsonResponse($addressList);
  }

  /**
   * Set address as default address for the user.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function setDefaultAddress(Request $request) {
    $data = json_decode($request->getContent(), TRUE);
    $request->request->replace(is_array($data) ? $data : []);
    $response = [];
    try {
      $uid = $this->currentUser->getAccount()->id();
      if ($this->spcHelper->setDefaultAddress($data['address_id'], $uid)) {
        $response['data'] = $this->spcHelper->getAddressListByUid($uid);
        $response['status'] = TRUE;
      }
      else {
        $response['status'] = FALSE;
      }
    }
    catch (\Exception $e) {
      $response['status'] = FALSE;
    }

    return new JsonResponse($response);
  }

  /**
   * Delete the address of the user.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Reauest object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function deleteAddress(Request $request) {
    $response = [];
    $data = json_decode($request->getContent(), TRUE);
    $request->request->replace(is_array($data) ? $data : []);

    try {
      $uid = $this->currentUser->getAccount()->id();
      $profile = $this->entityTypeManager->getStorage('profile')->load($data['address_id']);
      // If address belongs to the current user.
      if ($profile && $profile->getOwnerId() == $uid) {
        // If user tyring to delete default address.
        if ($profile->isDefault()) {
          $response['status'] = FALSE;
        }
        else {
          // Delete the address.
          $profile->delete();
          $response['status'] = TRUE;
          $response['data'] = $this->spcHelper->getAddressListByUid($uid);
        }
      }
      else {
        $response['status'] = FALSE;
      }
    }
    catch (\Exception $e) {
      $response['status'] = FALSE;
    }

    return new JsonResponse($response);
  }

  /**
   * Get the click n collect stores for given cart and lat/long.
   *
   * @param int $cart_id
   *   The cart id.
   * @param float $lat
   *   The latitude.
   * @param float $lon
   *   The longitude.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getCncStoresJson($cart_id, $lat = NULL, $lon = NULL) {
    $stores = $this->clickCollect->getCartStores($cart_id, $lat, $lon);
    return new JsonResponse($stores);
  }

}
