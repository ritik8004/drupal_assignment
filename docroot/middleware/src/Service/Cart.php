<?php

namespace App\Service;

use App\Service\CheckoutCom\APIWrapper;
use App\Service\Config\SystemSettings;
use App\Service\Knet\KnetHelper;
use App\Service\Magento\MagentoApiWrapper;
use App\Service\Magento\MagentoInfo;
use App\Service\Magento\CartActions;
use App\Service\CheckoutCom\CustomerCards;

/**
 * Class Cart.
 */
class Cart {

  /**
   * Static cache for cart.
   *
   * @var array
   */
  protected static $cart = [];

  /**
   * The cart storage key.
   */
  const SESSION_STORAGE_KEY = 'middleware_cart_id';

  /**
   * Magento service.
   *
   * @var \App\Service\Magento\MagentoInfo
   */
  protected $magentoInfo;

  /**
   * Magento API Wrapper.
   *
   * @var \App\Service\Magento\MagentoApiWrapper
   */
  protected $magentoApiWrapper;

  /**
   * Utility.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * Checkout.com API Wrapper.
   *
   * @var \App\Service\CheckoutCom\APIWrapper
   */
  protected $checkoutComApi;

  /**
   * K-Net Helper.
   *
   * @var \App\Service\Knet\KnetHelper
   */
  protected $knetHelper;

  /**
   * Payment Data provider.
   *
   * @var \App\Service\PaymentData
   */
  protected $paymentData;

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

  /**
   * Service for session.
   *
   * @var \App\Service\SessionStorage
   */
  protected $session;

  /**
   * Checkout.com API Wrapper.
   *
   * @var \App\Service\CheckoutCom\CustomerCards
   */
  protected $customerCards;

  /**
   * Cart constructor.
   *
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API Wrapper.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \App\Service\CheckoutCom\APIWrapper $checkout_com_api
   *   Checkout.com API Wrapper.
   * @param \App\Service\Knet\KnetHelper $knet_helper
   *   K-Net Helper.
   * @param \App\Service\PaymentData $payment_data
   *   Payment Data provider.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   * @param \App\Service\SessionStorage $session
   *   Service for session.
   * @param \App\Service\CheckoutCom\CustomerCards $customer_cards
   *   Checkout.com API Wrapper.
   */
  public function __construct(
    MagentoInfo $magento_info,
    MagentoApiWrapper $magento_api_wrapper,
    Utility $utility,
    APIWrapper $checkout_com_api,
    KnetHelper $knet_helper,
    PaymentData $payment_data,
    SystemSettings $settings,
    SessionStorage $session,
    CustomerCards $customer_cards
  ) {
    $this->magentoInfo = $magento_info;
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->utility = $utility;
    $this->checkoutComApi = $checkout_com_api;
    $this->knetHelper = $knet_helper;
    $this->paymentData = $payment_data;
    $this->settings = $settings;
    $this->session = $session;
    $this->customerCards = $customer_cards;
  }

  /**
   * Wrapper function to get cart id from session.
   *
   * @return int|null
   *   Cart id.
   */
  public function getCartId() {
    return $this->session->getDataFromSession(self::SESSION_STORAGE_KEY);
  }

  /**
   * Get cart by cart id.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getCart() {
    if (!empty(self::$cart)) {
      return self::$cart;
    }

    $cart_id = $this->getCartId();
    if (empty($cart_id)) {
      return NULL;
    }

    $url = sprintf('carts/%d/getCart', $cart_id);

    try {
      self::$cart = $this->magentoApiWrapper->doRequest('GET', $url);
      return self::$cart;
    }
    catch (\Exception $e) {
      self::$cart = NULL;

      if (strpos($e->getMessage(), 'No such entity with cartId') > -1) {
        $this->session->updateDataInSession(self::SESSION_STORAGE_KEY, NULL);
      }

      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Create a new cart and get cart id.
   *
   * @return mixed
   *   Cart id.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function createCart(int $customer_id = 0) {
    if (!empty($this->getCartId())) {
      // Validate the cart again to ensure session data is not corrupt.
      $data = $this->getCart();
      if (empty($data['error'])) {
        return $this->getCartId();
      }
    }

    $url = $customer_id > 0
      ? str_replace('{customerId}', $customer_id, 'customers/{customerId}/carts')
      : 'carts';

    try {
      $cart_id = (int) $this->magentoApiWrapper->doRequest('POST', $url);
      $this->session->updateDataInSession(self::SESSION_STORAGE_KEY, $cart_id);
      return $cart_id;
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Add/Update/Remove item in cart.
   *
   * @param string $sku
   *   Sku.
   * @param int|null $quantity
   *   Quantity.
   * @param string $action
   *   Action to be performed (add/update/remove).
   * @param array $options
   *   Options array.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function addUpdateRemoveItem(string $sku, ?int $quantity, string $action, array $options = []) {
    $option_data = [];
    // If options data available.
    if (!empty($options)) {
      foreach ($options as &$op) {
        $op = (object) $op;
      }
      $option_data = [
        'extension_attributes' => (object) [
          'configurable_item_options' => $options,
        ],
      ];
    }
    $data['items'][] = (object) [
      'sku' => $sku,
      'qty' => $quantity ?? 1,
      'quote_id' => (string) $this->getCartId(),
      'product_option' => (object) $option_data,
    ];
    $data['extension'] = (object) [
      'action' => $action,
    ];

    return $this->updateCart($data);
  }

  /**
   * Apply promo on the cart.
   *
   * @param string|null $promo
   *   Promo to apply.
   * @param string $action
   *   Action to perform (promo apply/remove).
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function applyRemovePromo(?string $promo, string $action) {
    $data = [
      'extension' => (object) [
        'action' => $action,
      ],
    ];

    if ($promo) {
      $data['coupon'] = $promo;
    }

    return $this->updateCart($data);
  }

  /**
   * Format shipping info for api call.
   *
   * @param array $shippig_info
   *   Shipping info.
   *
   * @return array
   *   Formatted shipping info for api.
   */
  public function prepareShippingData(array $shippig_info) {
    // If address id available.
    if (!empty($shippig_info['address_id'])) {
      $data['address_id'] = $shippig_info['address_id'];
    }
    else {
      $static_fields = $shippig_info['static'];
      unset($shippig_info['static']);
      $custom_attributes = [];
      foreach ($shippig_info as $field_name => $val) {
        $custom_attributes[] = [
          'attribute_code' => $field_name,
          'value' => $val,
        ];
      }

      $fields_data = [];
      foreach ($static_fields as $key => $field) {
        $fields_data[$key] = $field;
      }

      $fields_data = array_merge($fields_data, ['custom_attributes' => $custom_attributes]);
      $data = [
        'address' => $fields_data,
      ];
    }

    return $data;
  }

  /**
   * Adding shipping on the cart.
   *
   * @param array $shipping_data
   *   Shipping address info.
   * @param string $action
   *   Action to perform.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function addShippingInfo(array $shipping_data, string $action) {
    $data = [
      'extension' => (object) [
        'action' => $action,
      ],
    ];

    // If shipping address add by address id.
    if (!empty($shipping_data['customer_address_id'])) {
      $fields_data = $shipping_data['address'];
      $carrier_info = $shipping_data['carrier_info'];
    }
    else {
      $carrier_info = $shipping_data['carrier_info'];
      $fields_data = $this->formatAddressForShippingBilling($shipping_data);
    }

    $data['shipping']['shipping_address'] = $fields_data;
    $data['shipping']['shipping_carrier_code'] = $carrier_info['code'];
    $data['shipping']['shipping_method_code'] = $carrier_info['method'];

    $cart = $this->updateCart($data);

    // If cart update has error.
    if ($cart['error']) {
      return $cart;
    }

    // If cart has no customer or email provided is different,
    // then create and assign customer to the cart.
    if (empty($shipping_data['customer_address_id']) && (empty($cart['cart']['customer']['id']) ||
      $cart['cart']['customer']['email'] !== $data['shipping']['shipping_address']['email'])) {
      $customer = $this->createCustomer($data['shipping']['shipping_address']);
      // If any error.
      if ($customer['error']) {
        return $customer;
      }

      $associated_customer = $this->associateCartToCustomer($customer['id']);
      // If any error.
      if ($associated_customer['error']) {
        return $associated_customer;
      }
    }

    return $this->updateBilling($data['shipping']['shipping_address']);
  }

  /**
   * Format the address array.
   *
   * Format the address array so that it can be used to update billing or
   * shipping address in the cart.
   *
   * @param array $address
   *   Address array.
   *
   * @return array
   *   Formatted address array.
   */
  public function formatAddressForShippingBilling(array $address) {
    $static_fields = $address['static'];
    // Unset static and carrier info if available.
    unset($address['static']);
    if (!empty($address['carrier_info'])) {
      unset($address['carrier_info']);
    }

    $custom_attributes = [];
    foreach ($address as $field_name => $val) {
      $custom_attributes[] = [
        'attributeCode' => $field_name,
        'value' => $val,
      ];
    }

    $fields_data = [];
    foreach ($static_fields as $key => $field) {
      $fields_data[$key] = $field;
    }

    $fields_data = array_merge($fields_data, ['customAttributes' => $custom_attributes]);
    if (!empty($address['street'])) {
      $fields_data['street'] = is_array($address['street'])
        ? $address['street']
        : [$address['street']];
    }

    return $fields_data;
  }

  /**
   * Add click n collect shipping on the cart.
   *
   * @param array $shipping_data
   *   Shipping address info.
   * @param string $action
   *   Action to perform.
   * @param bool $create_customer
   *   True to create customer, false otherwise.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function addCncShippingInfo(array $shipping_data, string $action, $create_customer = TRUE) {
    $data = [
      'extension' => (object) [
        'action' => $action,
      ],
    ];
    $store = $shipping_data['store'];
    $static_fields = array_merge($shipping_data['store']['cart_address'], $shipping_data['static']);
    // Unset as not needed in further processing.
    unset($static_fields['extension']);
    $carrier_info = $shipping_data['carrier_info'];

    $shipping_data = array_merge($shipping_data, $shipping_data['store']['cart_address']['extension']);
    // Unset as not needed in further processing.
    unset($shipping_data['carrier_info'], $shipping_data['static'], $shipping_data['store']);

    $custom_attributes = [];
    foreach ($shipping_data as $field_name => $val) {
      $custom_attributes[] = [
        'attributeCode' => $field_name,
        'value' => $val,
      ];
    }

    $fields_data = [];
    foreach ($static_fields as $key => $field) {
      $fields_data[$key] = ($key == 'street') ? [$field] : $field;
    }

    $fields_data = array_merge($fields_data, ['custom_attributes' => $custom_attributes]);
    $data['shipping']['shipping_address'] = $fields_data;
    $data['shipping']['shipping_carrier_code'] = $carrier_info['code'];
    $data['shipping']['shipping_method_code'] = $carrier_info['method'];
    $data['shipping']['extension_attributes'] = (object) [
      'click_and_collect_type' => !empty($store['rnc_available']) ? 'reserve_and_collect' : 'ship_to_store',
      'store_code' => $store['code'],
    ];

    if ($create_customer) {
      $customer = $this->createCustomer($data['shipping']['shipping_address']);
      if (!empty($customer['message'])) {
        return $this->getErrorResponse($customer['message'], 422);
      }
      $this->associateCartToCustomer($customer['id']);
    }
    return $this->updateCart($data);
  }

  /**
   * Update billing info on cart.
   *
   * @param array $billing_data
   *   Billing data.
   *
   * @return array
   *   Response data.
   */
  public function updateBilling(array $billing_data) {
    $data = [
      'extension' => (object) [
        'action' => CartActions::CART_BILLING_UPDATE,
      ],
    ];

    $data['billing'] = $billing_data;

    return $this->updateCart($data);
  }

  /**
   * Create customer in magento.
   *
   * @param array $customer_data
   *   Customer data.
   *
   * @return array|mixed
   *   Json decoded response.
   */
  public function createCustomer(array $customer_data) {
    $url = 'customers';

    try {
      $data['customer'] = [
        'email' => $customer_data['email'],
        'firstname' => $customer_data['firstname'] ?? '',
        'lastname' => $customer_data['lastname'] ?? '',
        'prefix' => $customer_data['prefix'] ?? '',
        'dob' => $customer_data['dob'] ?? '',
        'groupId' => $customer_data['group_id'] ?? 1,
        'store_id' => $this->magentoInfo->getMagentoStoreId(),
      ];

      return $this->magentoApiWrapper->doRequest('POST', $url, ['json' => (object) $data]);
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Adds a customer to cart.
   *
   * @param int $customer_id
   *   Customer id.
   *
   * @return mixed
   *   Response.
   */
  public function associateCartToCustomer(int $customer_id) {
    $cart_id = $this->getCartId();
    $url = sprintf('carts/%d/associate-cart', $cart_id);

    try {
      $data = [
        'customerId' => $customer_id,
        'cartId' => $cart_id,
        'store_id' => $this->magentoInfo->getMagentoStoreId(),
      ];

      return $this->magentoApiWrapper->doRequest('POST', $url, ['json' => (object) $data]);
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Adding payment on the cart.
   *
   * @param array $data
   *   Payment info.
   * @param array $extension
   *   (Optional) Cart extension.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function updatePayment(array $data, array $extension = []) {
    $extension['action'] = 'update payment';
    $update = [
      'extension' => (object) $extension,
    ];

    $update['payment'] = [
      'method' => $data['method'],
      'additional_data' => $data['additional_data'],
    ];

    return $this->updateCart($update);
  }

  /**
   * Process payment data before placing order.
   *
   * @param string $method
   *   Payment method.
   * @param array $additional_info
   *   Additional info.
   *
   * @return array
   *   Processed payment data.
   *
   * @throws \Exception
   */
  public function processPaymentData(string $method, array $additional_info) {
    $additional_data = [];

    // Method specific code.
    switch ($method) {
      case 'knet':
        $cart = $this->getCart();

        $response = $this->knetHelper->initKnetRequest(
          $cart['totals']['grand_total'],
          $this->getCartId(),
          $cart['cart']['extension_attributes']['real_reserved_order_id'],
          $this->getCartCustomerId()
        );

        if (isset($response['redirectUrl']) && !empty($response['redirectUrl'])) {
          $this->paymentData->setPaymentData($this->getCartId(), $response['id'], $response['data']);
          throw new \Exception($response['redirectUrl'], 302);
        }

        throw new \Exception('Failed to initiate K-Net request.', 500);

      case 'checkout_com':
        $process_3d = FALSE;
        $payment_data = [];
        $end_point = '';
        // Process for new 3D card.
        if ($additional_info['card_type'] == 'new') {
          $additional_data = [
            'card_token_id' => $additional_info['id'],
            'udf3' => $additional_info['udf3'],
          ];

          // Validate bin if MADA enabled.
          $additional_data['udf1'] = $this->checkoutComApi->validateMadaBin($additional_info['card']['bin'])
            ? 'MADA'
            : '';

          $process_3d = $additional_data['udf1'] || $this->checkoutComApi->is3dForced();
          $payment_data = $additional_data;
          $end_point = APIWrapper::ENDPOINT_CARD_PAYMENT;
        }
        elseif ($additional_info['card_type'] == 'existing' && !empty($additional_info['cvv'])) {
          $card = $this->customerCards->getGiveCardInfo($this->getCartCustomerId(), $additional_info['id']);
          if ($card) {
            $process_3d = TRUE;
            $payment_data = [
              'cardId' => $card['gateway_token'],
              'cvv' => $additional_info['cvv'],
              'udf2' => APIWrapper::CARD_ID_CHARGE,
            ];
            $end_point = APIWrapper::ENDPOINT_CARD_PAYMENT;
          }
        }

        // Process 3D if MADA or 3D Forced.
        if ($process_3d && !empty($payment_data) && !empty($end_point)) {
          $response = $this->checkoutComApi->request3dSecurePayment(
            $this->getCart(),
            $payment_data,
            $end_point
          );

          if (isset($response['responseCode'])
              && $response['responseCode'] == APIWrapper::SUCCESS
              && !empty($response[APIWrapper::REDIRECT_URL])) {
            // We will use this again to redirect back to Drupal.
            $response['langcode'] = $this->settings->getRequestLanguage();
            $this->paymentData->setPaymentData($this->getCartId(), $response['id'], $response);
            throw new \Exception($response[APIWrapper::REDIRECT_URL], 302);
          }

          throw new \Exception('Failed to initiate 3D request.', 500);
        }

        break;
    }

    return $additional_data;
  }

  /**
   * Common function for updating cart.
   *
   * @param array $data
   *   Data to update for cart.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function updateCart(array $data) {
    $cart_id = $this->getCartId();
    $url = sprintf('carts/%d/updateCart', $cart_id);

    try {
      static::$cart[$cart_id] = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => (object) $data]);
      return static::$cart[$cart_id];
    }
    catch (\Exception $e) {
      static::$cart = NULL;

      // Re-set cart id in session if exception is for cart not found.
      if (strpos($e->getMessage(), 'No such entity with cartId') > -1) {
        $this->session->updateDataInSession(self::SESSION_STORAGE_KEY, NULL);
      }

      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Gets shipping methods.
   *
   * @param array $data
   *   Data for getting shipping method.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function shippingMethods(array $data) {
    if (empty($data['address']['country_id'])) {
      return [];
    }

    $url = sprintf('carts/%d/estimate-shipping-methods', $this->getCartId());

    try {
      return $this->magentoApiWrapper->doRequest('POST', $url, ['json' => $data]);
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Gets payment methods.
   *
   * @return array
   *   Payment method list.
   */
  public function getPaymentMethods() {
    $url = sprintf('carts/%d/payment-methods', $this->getCartId());

    try {
      return $this->magentoApiWrapper->doRequest('GET', $url);
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Place order.
   *
   * @param array $data
   *   Post data.
   *
   * @return array
   *   Status.
   */
  public function placeOrder(array $data) {
    $url = sprintf('carts/%d/order', $this->getCartId());

    try {
      $result = $this->magentoApiWrapper->doRequest('PUT', $url, ['json' => $data]);
      $order_id = (int) str_replace('"', '', $result);

      // Remove cart id from session.
      $this->session->updateDataInSession(self::SESSION_STORAGE_KEY, NULL);

      // Set order in session for later use.
      $this->session->updateDataInSession(Orders::SESSION_STORAGE_KEY, $order_id);

      return ['success' => TRUE, 'order_id' => $order_id];
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Check if a customer by given email exists or not.
   *
   * @param string $email
   *   Email address.
   *
   * @return mixed
   *   Response.
   */
  public function customerCheckByMail(string $email) {
    $url = 'customers/search';
    $query['query'] = [
      'searchCriteria[filterGroups][0][filters][0][field]' => 'email',
      'searchCriteria[filterGroups][0][filters][0][value]' => $email,
      'searchCriteria[filterGroups][0][filters][0][condition_type]' => 'eq',
      'searchCriteria[filterGroups][1][filters][0][field]' => 'store_id',
      'searchCriteria[filterGroups][1][filters][0][value]' => $this->magentoInfo->getMagentoStoreId(),
      'searchCriteria[filterGroups][1][filters][0][condition_type]' => 'in',
    ];

    return $this->magentoApiWrapper->doRequest('GET', $url, $query);
  }

  /**
   * Wrapper function to get cleaned cart data to log.
   *
   * @param array $cart
   *   Cart data.
   *
   * @return string
   *   Cleaned cart data as JSON string.
   */
  public function getCartDataToLog(array $cart) {
    // TODO: Remove sensitive info.
    return json_encode($cart);
  }

  /**
   * Return customer id from current session.
   *
   * @return int|null
   *   Return customer id or null.
   */
  public function getCartCustomerId() {
    $cart = $this->getCart();

    if (isset($cart, $cart['cart']['customer'], $cart['cart']['customer']['id'])) {
      return $cart['cart']['customer']['id'];
    }

    return NULL;
  }

}
