<?php

namespace App\Service;

use App\Service\Drupal\Drupal;
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
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

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
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \App\Service\Orders $orders
   *   Orders service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(Cart $cart,
                              Drupal $drupal,
                              Orders $orders,
                              Utility $utility) {
    $this->cart = $cart;
    $this->drupal = $drupal;
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
    $address = $order['shipping']['commerce_address'];

    if (strpos($order['shipping']['method'], 'click_and_collect') === 0) {
      $store = $this->drupal->getStoreInfo($order['shipping']['extension_attributes']['store_code']);

      // Get the stores list via Drupal only to ensure we get other validations
      // and configuration checks applied, for eg. if CNC is disabled complete
      // from Drupal Config.
      $availableStores = $this->drupal->getCartStores($this->cart->getCartId(), $store['lat'], $store['lng']);
      $availableStoreCodes = array_column($availableStores ?? [], 'code');
      if (in_array($store['code'], $availableStoreCodes)) {
        return $this->selectCnc($store, $address, $order['billing_commerce_address']);
      }

      return FALSE;
    }

    $methods = $this->cart->getHomeDeliveryShippingMethods(['address' => $address]);

    foreach ($methods as $method) {
      if (strpos($order['shipping']['method'], $method['carrier_code']) === 0
        && strpos($order['shipping']['method'], $method['method_code']) !== FALSE) {
        return $this->selectHd($address, $method, $order['billing_commerce_address']);
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
   * @param array $billing
   *   Billing address.
   *
   * @return array|bool
   *   FALSE if something went wrong, updated cart data otherwise.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function selectHd(array $address, array $method, array $billing) {
    $shipping_data = [
      'customer_address_id' => $address['id'] ?? $address['customer_address_id'] ?? 0,
      'address' => $address,
      'carrier_info' => [
        'code' => $method['carrier_code'],
        'method' => $method['method_code'],
      ],
    ];

    $updated = $this->cart->addShippingInfo($shipping_data, CartActions::CART_SHIPPING_UPDATE, FALSE);
    if (isset($updated['error'])) {
      return FALSE;
    }

    $updated = $this->cart->updateBilling($billing);
    return $updated;
  }

  /**
   * Select Click and Collect store and method from possible defaults.
   *
   * @param array $store
   *   Store info.
   * @param array $address
   *   Shipping address from last order.
   * @param array $billing
   *   Billing address.
   *
   * @return array|bool
   *   FALSE if something went wrong, updated cart data otherwise.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function selectCnc(array $store, array $address, array $billing) {
    $data = [
      'extension' => (object) [
        'action' => CartActions::CART_SHIPPING_UPDATE,
      ],
    ];
    $data['shipping']['shipping_address'] = $address;
    $data['shipping']['shipping_carrier_code'] = 'click_and_collect';
    $data['shipping']['shipping_method_code'] = 'click_and_collect';
    $data['shipping']['extension_attributes'] = [
      'click_and_collect_type' => !empty($store['rnc_available']) ? 'reserve_and_collect' : 'ship_to_store',
      'store_code' => $store['code'],
    ];

    $updated = $this->cart->updateCart($data);
    if (isset($updated['error'])) {
      return FALSE;
    }

    $updated = $this->cart->updateBilling($billing);
    return $updated;
  }

}
