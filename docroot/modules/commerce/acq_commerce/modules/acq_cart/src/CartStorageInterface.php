<?php

namespace Drupal\acq_cart;

/**
 * Defines the interface for storing carts.
 *
 * @package Drupal\acq_cart
 */
interface CartStorageInterface {

  // The cart storage key.
  public const STORAGE_KEY = 'acq_cart';

  /**
   * Restores the cart to what is available in Magento.
   *
   * @param int $cart_id
   *   Cart Id to restore. We don't rely on other functions as cart is already
   *   corrupt when we call this function.
   *
   * @return bool
   *   Whether cart restore successfull or not.
   */
  public function restoreCart($cart_id);

  /**
   * Clears the cart details in session and cookies.
   */
  public function clearCart();

  /**
   * Clears the shipping method details in cart stored in session.
   */
  public function clearShippingMethodSession();

  /**
   * Gets the current card ID.
   *
   * @param bool $create_new
   *   Create new cart if no cart exists for the current session.
   *
   * @return int
   *   Current Cart Id.
   */
  public function getCartId($create_new);

  /**
   * Adds the given cart to storage.
   *
   * @param \Drupal\acq_cart\CartInterface $cart
   *   The cart object.
   */
  public function addCart(CartInterface $cart);

  /**
   * Gets cart from storage.
   *
   * @param bool $create_new
   *   Create new cart if no cart exists for the current session.
   *
   * @return \Drupal\acq_cart\CartInterface
   *   The current cart.
   */
  public function getCart($create_new);

  /**
   * Get skus of current cart items.
   *
   * @return array
   *   Items in the current cart.
   */
  public function getCartSkus();

  /**
   * Updates the current cart in storage.
   *
   * @param bool $create_new
   *   Create new cart if no cart exists for the current session.
   *
   * @return \Drupal\acq_cart\Cart
   *   Updated cart.
   */
  public function updateCart($create_new);

  /**
   * Creates a cart for storage.
   */
  public function createCart();

  /**
   * Associate the current cart in storage with a given customer.
   *
   * @param int $customer_id
   *   Customer ID.
   * @param string $customer_email
   *   Customer E-Mail.
   */
  public function associateCart($customer_id, $customer_email);

}
