<?php

namespace App\Service;

use App\Service\CheckoutCom\APIWrapper;
use App\Service\Drupal\DrupalInfo;
use App\Service\Magento\MagentoApiWrapper;
use App\Service\Magento\MagentoInfo;
use App\Service\Magento\CartActions;

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
   * Service to get Drupal Info.
   *
   * @var \App\Service\Drupal\DrupalInfo
   */
  protected $drupalInfo;

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
   * Payment Data provider.
   *
   * @var \App\Service\PaymentData
   */
  protected $paymentData;

  /**
   * Cart constructor.
   *
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API Wrapper.
   * @param \App\Service\Drupal\DrupalInfo $drupal_info
   *   Service to get Drupal Info.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \App\Service\CheckoutCom\APIWrapper $checkout_com_api
   *   Checkout.com API Wrapper.
   * @param \App\Service\PaymentData $payment_data
   *   Payment Data provider.
   */
  public function __construct(MagentoInfo $magento_info,
                              MagentoApiWrapper $magento_api_wrapper,
                              DrupalInfo $drupal_info,
                              Utility $utility,
                              APIWrapper $checkout_com_api,
                              PaymentData $payment_data) {
    $this->magentoInfo = $magento_info;
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->drupalInfo = $drupal_info;
    $this->utility = $utility;
    $this->checkoutComApi = $checkout_com_api;
    $this->paymentData = $payment_data;
  }

  /**
   * Get cart by cart id.
   *
   * @param int $cart_id
   *   Cart id.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getCart(int $cart_id) {
    if (isset(static::$cart[$cart_id])) {
      return static::$cart[$cart_id];
    }

    $url = sprintf('carts/%d/getCart', $cart_id);

    try {
      return $this->magentoApiWrapper->doRequest('GET', $url);
    }
    catch (\Exception $e) {
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
  public function createCart() {
    $url = 'carts';
    try {
      return $this->magentoApiWrapper->doRequest('POST', $url);
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Add/Update/Remove item in cart.
   *
   * @param int $cart_id
   *   Cart id.
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
  public function addUpdateRemoveItem(int $cart_id, string $sku, ?int $quantity, string $action, array $options = []) {
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
      'quote_id' => (string) $cart_id,
      'product_option' => (object) $option_data,
    ];
    $data['extension'] = (object) [
      'action' => $action,
    ];

    return $this->updateCart($data, $cart_id);
  }

  /**
   * Apply promo on the cart.
   *
   * @param int $cart_id
   *   Cart id.
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
  public function applyRemovePromo(int $cart_id, ?string $promo, string $action) {
    $data = [
      'extension' => (object) [
        'action' => $action,
      ],
    ];

    if ($promo) {
      $data['coupon'] = $promo;
    }

    return $this->updateCart($data, $cart_id);
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
   * @param int $cart_id
   *   Cart id.
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
  public function addShippingInfo(int $cart_id, array $shipping_data, string $action) {
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
      $static_fields = $shipping_data['static'];
      $carrier_info = $shipping_data['carrier_info'];
      unset($shipping_data['carrier_info'], $shipping_data['static']);
      $custom_attributes = [];
      foreach ($shipping_data as $field_name => $val) {
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
      if (!empty($shipping_data['street'])) {
        $fields_data['street'] = is_array($shipping_data['street'])
          ? $shipping_data['street']
          : [$shipping_data['street']];
      }
    }
    $data['shipping']['shipping_address'] = $fields_data;
    $data['shipping']['shipping_carrier_code'] = $carrier_info['code'];
    $data['shipping']['shipping_method_code'] = $carrier_info['method'];

    $cart = $this->updateCart($data, $cart_id);

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

      $associated_customer = $this->associateCartToCustomer($cart_id, $customer['id']);
      // If any error.
      if ($associated_customer['error']) {
        return $associated_customer;
      }
    }

    return $this->updateBilling($cart_id, $data['shipping']['shipping_address']);
  }

  /**
   * Add click n collect shipping on the cart.
   *
   * @param int $cart_id
   *   Cart id.
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
  public function addCncShippingInfo(int $cart_id, array $shipping_data, string $action, $create_customer = TRUE) {
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
      $this->associateCartToCustomer($cart_id, $customer['id']);
    }
    return $this->updateCart($data, $cart_id);
  }

  /**
   * Update billing info on cart.
   *
   * @param int $cart_id
   *   Cart id.
   * @param array $billing_data
   *   Billing data.
   *
   * @return array
   *   Response data.
   */
  public function updateBilling(int $cart_id, array $billing_data) {
    $data = [
      'extension' => (object) [
        'action' => CartActions::CART_BILLING_UPDATE,
      ],
    ];

    $data['billing'] = $billing_data;

    return $this->updateCart($data, $cart_id);
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
   * @param int $cart_id
   *   Cart id.
   * @param int $customer_id
   *   Customer id.
   *
   * @return mixed
   *   Response.
   */
  public function associateCartToCustomer(int $cart_id, int $customer_id) {
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
   * @param int $cart_id
   *   Cart id.
   * @param array $data
   *   Payment info.
   * @param array $extension
   *   Cart extension.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function updatePayment(int $cart_id, array $data, array $extension) {
    $update = [
      'extension' => (object) $extension,
    ];

    $update['payment'] = [
      'method' => $data['method'],
      'additional_data' => $data['additional_data'],
    ];

    return $this->updateCart($update, $cart_id);
  }

  /**
   * Process payment data before placing order.
   *
   * @param int $cart_id
   *   Cart ID.
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
  public function processPaymentData(int $cart_id, string $method, array $additional_info) {
    $additional_data = [];

    // Method specific code. @TODO: Find better way to handle this.
    switch ($method) {
      case 'checkout_com':
        $additional_data = [
          'card_token_id' => $additional_info['id'],
          'udf3' => NULL,
        ];

        // Validate bin if MADA enabled.
        $additional_data['udf1'] = $this->checkoutComApi->validateMadaBin($additional_info['card']['bin'])
          ? 'MADA'
          : '';

        // Process 3D if MADA or 3D Forced.
        if ($additional_data['udf1'] || $this->checkoutComApi->is3dForced()) {
          $response = $this->checkoutComApi->request3dSecurePayment(
            $this->getCart($cart_id),
            $additional_data,
            APIWrapper::ENDPOINT_AUTHORIZE_PAYMENT
          );

          if (isset($response['responseCode'])
            && $response['responseCode'] == APIWrapper::SUCCESS
            && !empty($response[APIWrapper::REDIRECT_URL])) {
            // We will use this again to redirect back to Drupal.
            $response['langcode'] = $this->drupalInfo->getDrupalLangcode();
            $this->paymentData->setPaymentData($cart_id, $response['id'], $response);
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
   * @param int $cart_id
   *   Cart id.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function updateCart(array $data, int $cart_id) {
    $url = sprintf('carts/%d/updateCart', $cart_id);

    try {
      static::$cart[$cart_id] = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => (object) $data]);
      return static::$cart[$cart_id];
    }
    catch (\Exception $e) {
      static::$cart[$cart_id] = NULL;

      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Gets shipping methods.
   *
   * @param array $data
   *   Data for getting shipping method.
   * @param int $cart_id
   *   Cart id.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function shippingMethods(array $data, int $cart_id) {
    if (empty($data['address']['country_id'])) {
      return [];
    }

    $url = sprintf('carts/%d/estimate-shipping-methods', $cart_id);

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
   * @param int $cart_id
   *   Cart ID.
   *
   * @return array
   *   Payment method list.
   */
  public function getPaymentMethods(int $cart_id) {
    $url = sprintf('carts/%d/payment-methods', $cart_id);

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
   * @param int $cart_id
   *   Cart ID.
   * @param array $data
   *   Post data.
   *
   * @return mixed
   *   Status.
   */
  public function placeOrder(int $cart_id, array $data) {
    $url = sprintf('carts/%d/order', $cart_id);

    try {
      return $this->magentoApiWrapper->doRequest('PUT', $url, ['json' => $data]);
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

}
