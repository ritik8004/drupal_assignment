<?php

namespace App\Service;

use App\Service\Magento\MagentoInfo;

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
