<?php

namespace App\Service;

use App\Service\Magento\CartActions;

/**
 * Class CheckoutDefaults.
 *
 * Service to apply defaults to a cart on checkout.
 *
 * @package App\Service
 */
class CheckoutDefaults {

  /**
   * Cart service.
   *
   * @var \App\Service\Cart
   */
  protected $cart;

  /**
   * Orders service.
   *
   * @var \App\Service\Orders
   */
  protected $orders;

  /**
   * Utility.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * Orders constructor.
   *
   * @param \App\Service\Cart $cart
   *   Cart service.
   * @param \App\Service\Orders $orders
   *   Orders service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(Cart $cart,
                              Orders $orders,
                              Utility $utility) {
    $this->cart = $cart;
    $this->orders = $orders;
    $this->utility = $utility;
  }

  /**
   * Apply defaults to cart for customer.
   *
   * @param array $data
   *   Cart data.
   *
   * @return array|bool
   *   FALSE if something went wrong, updated cart data otherwise.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function applyDefaults(array $data) {
    if (!empty($data['shipping']['method'])) {
      return $data;
    }

    $order = $this->orders->getLastOrder($data['customer']['id']);
    if ($order) {
      if ($data = $this->applyDefaultShipping($order)) {
        $data['payment']['default'] = $this->getDefaultPaymentFromOrder($order) ?? '';
      }
    }
    elseif ($address = $this->getDefaultAddress($data)) {
      $methods = $this->cart->getHomeDeliveryShippingMethods(['address' => $address]);
      $data = $this->selectHd($address, reset($methods['methods']));
    }

    return $data;
  }

  /**
   * Apply shipping from last order.
   *
   * @param array $order
   *   Last Order details.
   *
   * @return array|bool
   *   FALSE if something went wrong, updated cart data otherwise.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function applyDefaultShipping(array $order) {
    $orderShippingMethod = explode('_', $order['shipping']['method'], 2);

    if ($orderShippingMethod[0] === 'click_and_collect') {
      // @TODO: Check once if we have the store available for new cart as well.
    }

    $address = $order['shipping']['address'];
    $methods = $this->cart->getHomeDeliveryShippingMethods(['address' => $address]);

    foreach ($methods as $method) {
      if ($method['carrier_code'] == $orderShippingMethod[0] && $method['method_code'] === $orderShippingMethod[1]) {
        return $this->selectHd($address, $method);
      }
    }

    return FALSE;
  }

  /**
   * Get payment method from last order.
   *
   * @param array $order
   *   Last Order details.
   *
   * @return bool|string
   *   FALSE if something went wrong, payment method name otherwise.
   */
  private function getDefaultPaymentFromOrder(array $order) {
    $orderPaymentMethod = $order['payment']['method'];

    $methods = $this->cart->getPaymentMethods();
    if (isset($methods['error'])) {
      return FALSE;
    }

    $methodNames = array_column($methods, 'code');
    if (!in_array($orderPaymentMethod, $methodNames)) {
      return FALSE;
    }

    return $orderPaymentMethod;
  }

  /**
   * Get default address from customer addresses.
   *
   * @param array $data
   *   Cart data.
   *
   * @return array|null
   *   Address if found.
   */
  private function getDefaultAddress(array $data) {
    $addresses = $data['customer']['addresses'] ?? [];

    // If no address available for the customer.
    if (empty($addresses)) {
      return NULL;
    }

    foreach ($addresses as $address) {
      // If address is set as default for shipping.
      if (empty($address['default_shipping'])) {
        continue;
      }

      return $address;
    }

    return reset($addresses);
  }

  /**
   * Select HD address and method from possible defaults.
   *
   * @param array $address
   *   Address array.
   * @param array $method
   *   Payment method.
   *
   * @return array|bool
   *   FALSE if something went wrong, updated cart data otherwise.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function selectHd(array $address, array $method) {
    $shipping_data = [
      'customer_address_id' => $address['id'] ?? $address['customer_address_id'] ?? 0,
      'address' => $address,
      'carrier_info' => [
        'code' => $method['carrier_code'],
        'method' => $method['method_code'],
      ],
    ];

    $updated = $this->cart->addShippingInfo($shipping_data, CartActions::CART_SHIPPING_UPDATE);
    if (isset($updated['error'])) {
      return FALSE;
    }

    return $updated;
  }

}
