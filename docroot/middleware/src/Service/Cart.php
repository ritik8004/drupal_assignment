<?php

namespace App\Service;

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
   * Cart constructor.
   *
   * @param \App\Service\Magento\MagentoInfo $magentoInfo
   *   Magento info service.
   */
  public function __construct(MagentoInfo $magentoInfo) {
    $this->magentoInfo = $magentoInfo;
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
    $client = $this->magentoInfo->getMagentoApiClient();
    $url = $this->magentoInfo->getMagentoUrl() . '/' . sprintf('carts/%d/getCart', $cart_id);

    try {
      $response = $client->request('GET', $url);
      $result = $response->getBody()->getContents();
      $cart = json_decode($result, TRUE);

      // In case magento not returns cart.
      if (!$cart || !isset($cart['cart'])) {
        throw new \Exception('Invalid cart id', 500);
      }

      return $cart;
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->getErrorResponse($e->getMessage(), $e->getCode());
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
    $client = $this->magentoInfo->getMagentoApiClient();
    $url = $this->magentoInfo->getMagentoUrl() . '/carts';
    try {
      $response = $client->request('POST', $url);
      $result = $response->getBody()->getContents();
      return json_decode($result, TRUE);
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->getErrorResponse($e->getMessage(), $e->getCode());
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

    $static_fields = $shipping_data['static'];
    $carrier_info = $shipping_data['carrier_info'];
    unset($shipping_data['carrier_info']);
    unset($shipping_data['static']);
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
    $data['shipping']['shipping_address'] = $fields_data;
    $data['shipping']['shipping_carrier_code'] = $carrier_info['code'];
    $data['shipping']['shipping_method_code'] = $carrier_info['method'];

    $customer = $this->createCustomer($data['shipping']['shipping_address']);
    $this->associateCartToCustomer($cart_id, $customer['id']);
    $this->updateCart($data, $cart_id);
    return $this->updateBilling($cart_id, $data['shipping']['shipping_address']);
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
    $client = $this->magentoInfo->getMagentoApiClient();
    $url = $this->magentoInfo->getMagentoUrl() . '/customers';

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

      $response = $client->request('POST', $url, ['json' => (object) $data]);
      $result = $response->getBody()->getContents();
      $rs = json_decode($result, TRUE);

      return $rs;
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Adds a customer to cart.
   *
   * @param int $cart_id
   *   Cart id.
   * @param int $customer_id
   *   Customer id.
   * @return mixed
   *   Response.
   */
  public function associateCartToCustomer(int $cart_id, int $customer_id) {
    $client = $this->magentoInfo->getMagentoApiClient();
    $url = $this->magentoInfo->getMagentoUrl() . '/' . sprintf('carts/%d/associate-cart', $cart_id);

    try {
      $data = [
        'customerId' => $customer_id,
        'cartId' => $cart_id,
        'store_id' => $this->magentoInfo->getMagentoStoreId(),
      ];
      $response = $client->request('POST', $url, ['json' => (object) $data]);
      $result = $response->getBody()->getContents();
      $rs = json_decode($result, TRUE);

      return $rs;
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->getErrorResponse($e->getMessage(), $e->getCode());
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
    $client = $this->magentoInfo->getMagentoApiClient();
    $url = $this->magentoInfo->getMagentoUrl() . '/' . sprintf('carts/%d/updateCart', $cart_id);

    try {
      $response = $client->request('POST', $url, ['json' => (object) $data]);
      $result = $response->getBody()->getContents();
      $cart = json_decode($result, TRUE);

      // In case magento not returns cart data.
      if (!$cart || !isset($cart['cart'])) {
        $message = 'Sorry, something went wrong. Please try again later.';
        if (!empty($cart['message'])) {
          $message = $cart['message'];
        }
        throw new \Exception($message, '500');
      }

      return $cart;
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->getErrorResponse($e->getMessage(), $e->getCode());
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
    $client = $this->magentoInfo->getMagentoApiClient();
    $shipping_method_url = '/carts/' . $cart_id . '/estimate-shipping-methods';
    $url = $this->magentoInfo->getMagentoUrl() . $shipping_method_url;
    try {
      $response = $client->request('POST', $url, ['json' => $data]);
      $result = $response->getBody()->getContents();
      return json_decode($result, TRUE);
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->getErrorResponse($e->getMessage(), $e->getCode());
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
    $client = $this->magentoInfo->getMagentoApiClient();
    $url = $this->magentoInfo->getMagentoUrl() . '/' . sprintf('carts/%d/payment-methods', $cart_id);

    try {
      $response = $client->request('GET', $url);
      $result = $response->getBody()->getContents();
      $payment_methods = json_decode($result, TRUE);
      return $payment_methods;
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->getErrorResponse($e->getMessage(), $e->getCode());
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
    $client = $this->magentoInfo->getMagentoApiClient();
    $url = $this->magentoInfo->getMagentoUrl() . '/' . sprintf('carts/%d/order', $cart_id);

    try {
      $response = $client->request('PUT', $url, ['json' => $data]);
      $result = $response->getBody()->getContents();
      $data = json_decode($result, TRUE);
      return $data;
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Method for error response.
   *
   * @param string $message
   *   Error message.
   * @param string $code
   *   Error code.
   *
   * @return array
   *   Error response array.
   */
  public function getErrorResponse(string $message, string $code) {
    return [
      'error' => TRUE,
      'error_message' => $message,
      'error_code' => $code,
    ];
  }

}
