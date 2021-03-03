<?php

namespace App\Service;

use App\Service\Drupal\Drupal;
use App\Service\Magento\CartActions;
use Psr\Log\LoggerInterface;

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
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

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
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   */
  public function __construct(Cart $cart,
                              Drupal $drupal,
                              Orders $orders,
                              Utility $utility,
                              LoggerInterface $logger) {
    $this->cart = $cart;
    $this->drupal = $drupal;
    $this->orders = $orders;
    $this->utility = $utility;
    $this->logger = $logger;
  }

  /**
   * Apply defaults to cart for customer.
   *
   * @param array $data
   *   Cart data.
   * @param int|string $uid
   *   Drupal User ID.
   *
   * @return array|bool
   *   FALSE if something went wrong, updated cart data otherwise.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function applyDefaults(array $data, $uid) {
    if (!empty($data['shipping']['method'])) {
      return $data;
    }

    // Get last order only for Drupal Customers.
    $order = $uid > 0
      ? $this->orders->getLastOrder($data['customer']['id'])
      : [];

    // Try to apply defaults from last order.
    if ($order) {
      // If cnc order but cnc is disabled.
      if (strpos($order['shipping']['method'], 'click_and_collect') !== FALSE
        && !$this->cart->getCncStatusForCart($data)) {
        return $data;
      }

      if ($response = $this->applyDefaultShipping($order)) {
        $response['payment']['default'] = $this->getDefaultPaymentFromOrder($order) ?? '';
        return $response;
      }
    }

    // Select default address from address book if available.
    if ($address = $this->getDefaultAddress($data)) {
      $methods = $this->cart->getHomeDeliveryShippingMethods(['address' => $address]);
      if (count($methods) && !isset($methods['error'])) {
        $this->logger->notice('Setting shipping/billing address from user address book. Address: @address Cart: @cart_id', [
          '@address' => json_encode($address),
          '@cart_id' => $this->cart->getCartId(),
        ]);
        return $this->selectHd($address, reset($methods), $address, $methods);
      }
    }

    // If address already available in cart, use it.
    if (isset($data['shipping']['address'], $data['shipping']['address']['country_id'])) {
      $address = $data['shipping']['address'];
      $methods = $this->cart->getHomeDeliveryShippingMethods(['address' => $address]);
      if (count($methods) && !isset($methods['error'])) {
        $this->logger->notice('Setting shipping/billing address from already available in cart. Address: @address Cart: @cart_id', [
          '@address' => json_encode($address),
          '@cart_id' => $this->cart->getCartId(),
        ]);
        return $this->selectHd($address, reset($methods), $address, $methods);
      }
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

      // We get a string value if store node is not present in Drupal. So in
      // that case we do not proceed.
      if (!is_array($store) || !isset($store['lat']) || !isset($store['lng'])) {
        return FALSE;
      }

      // Get the stores list via Drupal only to ensure we get other validations
      // and configuration checks applied, for eg. if CNC is disabled complete
      // from Drupal Config.
      $availableStores = $this->cart->getCartStores($store['lat'], $store['lng']);
      $availableStoreCodes = array_column($availableStores ?? [], 'code');
      $store_key = array_search($store['code'], $availableStoreCodes);
      if (($store_key !== FALSE) && ($store_key >= 0)) {
        return $this->selectCnc($availableStores[$store_key], $address, $order['billing_commerce_address']);
      }

      return FALSE;
    }

    if (empty($address['customer_address_id'])) {
      return FALSE;
    }

    $methods = $this->cart->getHomeDeliveryShippingMethods(['address' => $address]);
    if (count($methods) && !isset($methods['error'])) {
      foreach ($methods as $method) {
        if (isset($method['carrier_code'])
          && strpos($order['shipping']['method'], $method['carrier_code']) === 0
          && strpos($order['shipping']['method'], $method['method_code']) !== FALSE) {
          $this->logger->notice('Setting shipping/billing address from user last HD order. Cart: @cart_id', [
            '@cart_id' => $this->cart->getCartId(),
          ]);
          return $this->selectHd($address, reset($methods), $address, $methods);
        }
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
   * @param array $shipping_methods
   *   Shipping methods.
   *
   * @return array|bool
   *   FALSE if something went wrong, updated cart data otherwise.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function selectHd(array $address, array $method, array $billing, array $shipping_methods = []) {
    $shipping_data = [
      'customer_address_id' => $address['id'] ?? $address['customer_address_id'] ?? 0,
      'address' => $address,
      'carrier_info' => [
        'code' => $method['carrier_code'],
        'method' => $method['method_code'],
      ],
    ];

    // Validate address.
    $valid_address = $this->drupal->validateAddressAreaCity($address);
    // If address is not valid.
    if (empty($valid_address) || !$valid_address['address']) {
      return FALSE;
    }

    // Add log for shipping data we pass to magento update cart.
    $this->logger->notice('Shipping update default for HD. Data: @data Address: @address Cart: @cart_id', [
      '@data' => json_encode($shipping_data),
      '@address' => json_encode($address),
      '@method' => json_encode($method),
      '@cart_id' => $this->cart->getCartId(),
    ]);

    // If shipping address not contains proper address, don't process further.
    if (empty($shipping_data['address']['extension_attributes'])
      && empty($shipping_data['address']['custom_attributes'])) {
      return FALSE;
    }

    $updated = $this->cart->addShippingInfo($shipping_data, CartActions::CART_SHIPPING_UPDATE, FALSE);
    if (isset($updated['error'])) {
      return FALSE;
    }

    // Set shipping methods.
    if ($updated && !empty($updated['shipping']) && !empty($shipping_methods)) {
      $updated['shipping']['methods'] = $shipping_methods;
    }

    // Not use/assign default billing address if customer_address_id
    // is not available.
    if (empty($billing['customer_address_id'])) {
      return $updated;
    }

    // Add log for billing data we pass to magento update cart.
    $this->logger->notice('Billing update default for HD. Address: @address Cart: @cart_id', [
      '@address' => json_encode($billing),
      '@cart_id' => $this->cart->getCartId(),
    ]);

    // If billing address not contains proper address, don't process further.
    if (empty($billing['extension_attributes'])
      && empty($billing['custom_attributes'])) {
      return $updated;
    }

    $updated = $this->cart->updateBilling($billing);

    // If billing update has error.
    if (isset($updated['error'])) {
      return FALSE;
    }

    // Set shipping methods.
    if ($updated && !empty($updated['shipping']) && !empty($shipping_methods)) {
      $updated['shipping']['methods'] = $shipping_methods;
    }

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

    if (!isset($data['shipping']['shipping_address']['custom_attributes'])) {
      foreach ($data['shipping']['shipping_address']['extension_attributes'] ?? [] as $key => $value) {
        $data['shipping']['shipping_address']['custom_attributes'][] = [
          'attributeCode' => $key,
          'value' => $value,
        ];
      }
    }

    // Validate address.
    $valid_address = $this->drupal->validateAddressAreaCity($billing);
    // If address is not valid.
    if (empty($valid_address) || !$valid_address['address']) {
      return FALSE;
    }

    // Add log for shipping data we pass to magento update cart.
    $this->logger->notice('Shipping update default for CNC. Data: @data Address: @address Store: @store Cart: @cart_id', [
      '@data' => json_encode($data),
      '@address' => json_encode($address),
      '@store' => json_encode($store),
      '@cart_id' => $this->cart->getCartId(),
    ]);

    // If shipping address not contains proper data (extension info).
    if (empty($data['shipping']['shipping_address']['extension_attributes'])) {
      return FALSE;
    }

    $updated = $this->cart->updateCart($data);
    if (isset($updated['error'])) {
      return FALSE;
    }

    // Not use/assign default billing address if customer_address_id
    // is not available.
    if (empty($billing['customer_address_id'])) {
      return $updated;
    }

    // Add log for billing data we pass to magento update cart.
    $this->logger->notice('Billing update default for CNC. Address: @address Cart: @cart_id', [
      '@address' => json_encode($billing),
      '@cart_id' => $this->cart->getCartId(),
    ]);

    // If billing address not contains proper data (extension info).
    if (empty($billing['extension_attributes'])) {
      return FALSE;
    }

    $updated = $this->cart->updateBilling($billing);

    // If billing update has error.
    if (isset($updated['error'])) {
      return FALSE;
    }

    return $updated;
  }

}
