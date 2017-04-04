<?php

/**
 * @file
 * Contains \Drupal\acq_cart\CartInterface.
 */

namespace Drupal\acq_cart;

/**
 * Defines the interface for shopping carts.
 *
 * @package Drupal\acq_cart
 */
interface CartInterface {

  /**
   * Gets the cart ID.
   *
   * @return string|int|null
   *   The ID of the cart, or NULL if the cart does not have one.
   */
  public function id();

  /**
   * Gets the store ID.
   *
   * @return string|int|null
   *   The store ID of the cart, or NULL if the cart does not have one.
   */
  public function storeId();

  /**
   * Gets the customer ID.
   *
   * @return string|null
   *   The customer ID of the cart, or NULL if the cart does not have one.
   */
  public function customerId();

  /**
   * Gets the cart totals.
   *
   * @return mixed
   *   The value of the cart totals, NULL if not defined.
   */
  public function totals();

  /**
   * Gets the cart items.
   *
   * @return array
   *   An array of cart items.
   */
  public function items();

  /**
   * Adds an item to the cart
   *
   * @param string $sku
   *   The product SKU.
   * @param string|int $quantity
   *   The product quantity.
   */
  public function addItemToCart($sku, $quantity);

  /**
   * Removes an SKU from cart.
   *
   * @param string $sku
   *   The name of the SKU to be removed from the cart.
   */
  public function removeItemFromCart($sku);

  /**
   * Adds a raw item to the cart. Does not preform any validation
   * before appending to the cart items. Used for more complex items.
   *
   * @param array $item
   */
  public function addRawItemToCart(array $item);

  /**
   * Adds an array of items to the cart
   *
   * @param array
   *   The product array.
   */
  public function addItemsToCart($items);

  /**
   * Sets an array of items to the cart
   *
   * @param array
   *   The product array.
   */
  public function setItemsInCart($items);

  /**
   * Updates a cart item's quantity.
   *
   * @param string $sku
   *   The product SKU.
   * @param string|int $quantity
   *   The product quantity.
   */
  public function updateItemQuantity($sku, $quantity);

  /**
   * Gets the billing address.
   *
   * @return object
   *   The current billing address.
   */
  public function getBilling();

  /**
   * Sets the billing address.
   *
   * @param object|array $address
   *   The billing address
   */
  public function setBilling($address);

  /**
   * Gets the shipping address.
   *
   * @return object
   *   The current shipping address.
   */
  public function getShipping();

  /**
   * Gets the shipping method.
   *
   * @return array
   *   The current shipping method.
   */
  public function getShippingMethod();

  /**
   * Gets the shipping method as string for selects.
   *
   * @return string
   *   The current shipping method.
   */
  public function getShippingMethodAsString();


  /**
   * Gets the shipping method.
   *
   * @param string $carrier
   *   The current shipping carrier.
   * @param string $method
   *   The current shipping method.
   */
  public function setShippingMethod($carrier, $method);

  /**
   * Sets the shipping address.
   *
   * @param object|array $address
   *   The shipping address
   */
  public function setShipping($address);

  /**
   * Gets the payment method.
   *
   * @return array
   *   The payment method.
   */
  public function getPaymentMethod($full_details = TRUE);

  /**
   * Sets the payment method.
   *
   * @param string $payment_method
   *   The payment method
   * @param array $data
   *   The payment data
   */
  public function setPaymentMethod($payment_method, $data = []);

  /**
   * Gets the payment method data.
   *
   * @return array
   *   The payment method data.
   */
  public function getPaymentMethodData();

  /**
   * Sets the payment method data.
   *
   * @param array $data
   *   The payment method data
   */
  public function setPaymentMethodData($data = []);

  /**
   * Gets the current checkout step.
   *
   * @return string
   *   The checkout step.
   */
  public function getCheckoutStep();

  /**
   * Sets the current checkout step.
   *
   * @param string $step_id
   *   The id of the current checkout step.
   */
  public function setCheckoutStep($step_id);

  /**
   * Check if a cart is shippable.
   *
   * @return boolean
   *   TRUE if the cart is shippable, FALSE if not.
   */
  public function getShippable();

  /**
   * Check if a cart is shippable.
   *
   * @param boolean $shippable
   *   TRUE if the cart s shippable, FALSE if not.
   */
  public function setShippable($shippable);

  /**
   * Gets the coupon.
   *
   * @return string
   *   The coupon code.
   */
  public function getCoupon();

  /**
   * Sets the coupon.
   *
   * @param string $coupon
   *   The coupon code.
   */
  public function setCoupon($coupon);

  /**
   * Converts this cart to the customer cart provided.
   *
   * @param array $cart
   *   The coupon code.
   */
  public function convertToCustomerCart($cart);

  /**
   * Gets a cart property.
   *
   * @param string $property_name
   *   The name of the property to get; e.g., 'totals' or 'items'.
   *
   * @return mixed
   *   The value of the totals, NULL if not defined.
   */
  public function get($property_name);
}
