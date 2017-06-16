<?php

namespace Drupal\acq_cart;

use Drupal\acq_sku\Entity\SKU;

/**
 * Class Cart.
 *
 * @package Drupal\acq_cart
 */
class Cart implements CartInterface {

  /**
   * The magento cart object.
   *
   * @var object
   */
  protected $cart;

  /**
   * The current checkout step id.
   *
   * @var string
   */
  protected $checkoutStepId;

  /**
   * Whether or not the cart items can be shipped.
   *
   * @var bool
   */
  protected $shippable = FALSE;

  /**
   * The total quantity in the cart.
   *
   * @var int
   */
  protected $cartTotalCount = 0;

  /**
   * Constructor.
   *
   * @param object $cart
   *   The cart.
   */
  public function __construct($cart) {
    $this->cart = $cart;
    // Calculate the cart quantity items.
    //
    // There won't be any quantity count exists when we initialize the cart
    // object. So, we have to calculate it explicitly here.
    $this->updateCartItemsCount();
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    if (isset($this->cart, $this->cart->cart_id)) {
      return $this->cart->cart_id;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function storeId() {
    if (isset($this->cart, $this->cart->store_id)) {
      return $this->cart->store_id;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function customerId() {
    if (isset($this->cart, $this->cart->customer_id)) {
      return $this->cart->customer_id;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function customerEmail() {
    if (isset($this->cart, $this->cart->customer_email)) {
      return $this->cart->customer_email;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function totals() {
    if (isset($this->cart, $this->cart->totals)) {
      return $this->cart->totals;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   *
   * Process items to get their names from their plugins.
   */
  public function items() {
    if (!isset($this->cart, $this->cart->items)) {
      return [];
    }

    $items = $this->cart->items;

    foreach ($items as &$item) {
      if (!isset($item['sku'])) {
        continue;
      }

      $plugin_manager = \Drupal::service('plugin.manager.sku');
      $plugin = $plugin_manager->pluginInstanceFromType($item['product_type']);
      $sku = SKU::loadFromSku($item['sku']);

      if (empty($sku) || empty($plugin)) {
        continue;
      }

      $item['name'] = $plugin->cartName($sku, $item);
    }

    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function addItemToCart($sku, $quantity) {
    $items = $this->items();

    // Check if cart contains the same item.
    foreach ($items as $item) {
      if (!isset($item['sku'])) {
        continue;
      }

      if ($item['sku'] == $sku) {
        $new_qty = (int) $item['qty'] + (int) $quantity;
        if ($new_qty > 0) {
          $this->updateItemQuantity($sku, $new_qty);
        }
        else {
          $this->removeItemFromCart($sku);
        }
        return;
      }
    }

    $items[] = ['sku' => $sku, 'qty' => $quantity];

    $this->cart->items = $items;
  }

  /**
   * {@inheritdoc}
   */
  public function removeItemFromCart($sku) {
    $items = $this->items();
    foreach ($items as $key => &$item) {
      if (!isset($item['sku'])) {
        continue;
      }
      if ($item['sku'] == $sku) {
        unset($items[$key]);
        break;
      }
    }
    $this->cart->items = $items;
  }

  /**
   * {@inheritdoc}
   */
  public function addRawItemToCart(array $item) {
    $items = $this->items();

    $items[] = $item;

    $this->cart->items = $items;
  }

  /**
   * {@inheritdoc}
   */
  public function addItemsToCart(array $items) {
    foreach ($items as $item) {
      if (!isset($item['sku'])) {
        continue;
      }

      if (!isset($item['qty'])) {
        $item['qty'] = 1;
      }

      $this->addItemToCart($item['sku'], $item['qty']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setItemsInCart(array $items) {
    $this->cart->items = $items;
  }

  /**
   * {@inheritdoc}
   */
  public function updateItemQuantity($sku, $quantity) {
    $items = $this->items();

    foreach ($items as &$item) {
      if (!isset($item['sku'])) {
        continue;
      }

      if ($item['sku'] == $sku) {
        $item['qty'] = $quantity;
        break;
      }
    }

    $this->cart->items = $items;
  }

  /**
   * Get the total quantity of all items in the cart.
   *
   * @return int
   *   Return total number of items in the cart.
   */
  public function getCartItemsCount() {
    return $this->cartTotalCount;
  }

  /**
   * Calculate the cart items quantity.
   */
  public function updateCartItemsCount() {
    $this->cartTotalCount = 0;
    foreach ($this->items() as $item) {
      $this->cartTotalCount += $item['qty'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBilling() {
    $billing = new \stdClass();
    if (isset($this->cart, $this->cart->billing)) {
      $billing = $this->cart->billing;
    }
    return $billing;
  }

  /**
   * {@inheritdoc}
   */
  public function setBilling($address) {
    $this->cart->billing = (object) $address;

    if (isset($this->cart->billing->first_name)) {
      $this->cart->billing->firstname = $this->cart->billing->first_name;
      unset($this->cart->billing->first_name);
    }

    if (isset($this->cart->billing->last_name)) {
      $this->cart->billing->lastname = $this->cart->billing->last_name;
      unset($this->cart->billing->last_name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getShipping() {
    $shipping = new \stdClass();
    if (isset($this->cart, $this->cart->shipping)) {
      $shipping = $this->cart->shipping;
    }
    return $shipping;
  }

  /**
   * {@inheritdoc}
   */
  public function setShipping($address) {
    $this->cart->shipping = (object) $address;

    if (isset($this->cart->shipping->first_name)) {
      $this->cart->shipping->firstname = $this->cart->shipping->first_name;
      unset($this->cart->shipping->first_name);
    }

    if (isset($this->cart->shipping->last_name)) {
      $this->cart->shipping->lastname = $this->cart->shipping->last_name;
      unset($this->cart->shipping->last_name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setShippingMethod($carrier, $method) {
    $this->cart->carrier = [
      'carrier_code' => $carrier,
      'method_code' => $method,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getShippingMethod() {
    $shipping = NULL;

    if (isset($this->cart, $this->cart->carrier)) {
      $shipping = $this->cart->carrier;
    }

    return $shipping;
  }

  /**
   * {@inheritdoc}
   */
  public function getShippingMethodAsString() {
    if (isset($this->cart, $this->cart->carrier)) {
      $method = $this->cart->carrier;

      return implode(
        ',',
        [
          $method['carrier_code'],
          $method['method_code'],
        ]
      );
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethod($full_details = TRUE) {
    if (!isset($this->cart, $this->cart->payment)) {
      return [];
    }

    if ($full_details) {
      return $this->cart->payment;
    }

    return $this->cart->payment['method'];
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentMethod($payment_method, array $data = []) {
    $this->cart->payment['method'] = $payment_method;
    if (!empty($data)) {
      $this->cart->payment['additional_data'] = $data;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethodData() {
    if (isset($this->cart, $this->cart->payment)) {
      return $this->cart->payment['additional_data'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentMethodData(array $data = []) {
    if (isset($this->cart, $this->cart->payment)) {
      $this->cart->payment['additional_data'] = $data;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCheckoutStep() {
    return $this->checkoutStepId;
  }

  /**
   * {@inheritdoc}
   */
  public function setCheckoutStep($step_id) {
    $this->checkoutStepId = $step_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getShippable() {
    return $this->shippable;
  }

  /**
   * {@inheritdoc}
   */
  public function setShippable($shippable) {
    $this->shippable = $shippable;
  }

  /**
   * {@inheritdoc}
   */
  public function getCoupon() {
    if (isset($this->cart, $this->cart->coupon)) {
      return $this->cart->coupon;
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function setCoupon($coupon) {
    $this->cart->coupon = $coupon;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtension($key) {
    if (isset($this->cart, $this->cart->extension, $this->cart->extension[$key])) {
      return $this->cart->extension[$key];
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function setExtension($key, $value) {
    $this->cart->extension[$key] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCart() {
    if (isset($this->cart)) {
      $cart = $this->cart;

      // Don't set blank addresses, Magento doesn't like this.
      if (isset($cart->shipping) && empty($cart->shipping->street)) {
        unset($cart->shipping);
      }

      if (isset($cart->billing) && empty($cart->billing->street)) {
        unset($cart->billing);
      }

      return $this->cart;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function convertToCustomerCart(array $cart) {
    $this->cart->cart_id = $cart['cart_id'];
    $this->cart->customer_id = $cart['customer_id'];
    if (!empty($cart['customer_email'])) {
      $this->cart->customer_email = $cart['customer_email'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function get($property_name) {
    if (isset($this->cart, $this->cart->{$property_name})) {
      return $this->cart->{$property_name};
    }
    return NULL;
  }

}
