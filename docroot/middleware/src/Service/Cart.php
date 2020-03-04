<?php

namespace App\Service;

use App\Service\Magento\MagentoApiWrapper;
use App\Service\Magento\MagentoInfo;
use App\Service\Magento\CartActions;

/**
 * Class Cart.
 */
class Cart {

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
   * Cart constructor.
   *
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API Wrapper.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(MagentoInfo $magento_info,
                              MagentoApiWrapper $magento_api_wrapper,
                              Utility $utility) {
    $this->magentoInfo = $magento_info;
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->utility = $utility;
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
        $fields_data['street'] = $shipping_data['street'];
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
   * @param array $payment_data
   *   Payment info.
   * @param string $action
   *   Action to perform.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function updatePayment(int $cart_id, array $payment_data, string $action) {
    $data = [
      'extension' => (object) [
        'action' => $action,
      ],
    ];

    $data['payment'] = [
      'method' => $payment_data['payment']['method'],
      'additional_data' => $payment_data['payment']['additional_data'],
    ];
    return $this->updateCart($data, $cart_id);
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
      return $this->magentoApiWrapper->doRequest('POST', $url, ['json' => (object) $data]);
    }
    catch (\Exception $e) {
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
    $shipping_url = !empty($data['address_id'])
      ? 'estimate-shipping-methods-by-address-id'
      : 'estimate-shipping-methods';

    $url = 'carts/' . $cart_id . '/' . $shipping_url;

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

}
