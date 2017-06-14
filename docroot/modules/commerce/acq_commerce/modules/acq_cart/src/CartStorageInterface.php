<?php

namespace Drupal\acq_cart;

/**
 * Defines the interface for storing carts.
 *
 * @package Drupal\acq_cart
 */
interface CartStorageInterface {

  // The cart storage key.
  const STORAGE_KEY = 'acq_cart';

  /**
   * Restores the cart to what is available in Magento.
   *
   * @param int $cart_id
   *   Cart Id to restore. We don't rely on other functions as cart is already
   *   corrupt when we call this function.
   */
  public function restoreCart($cart_id);

  /**
   * Gets the current card ID.
   */
  public function getCartId();

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
   * @return \Drupal\acq_cart\CartInterface
   *   The current cart.
   */
  public function getCart();

  /**
   * Updates the current cart in storage.
   */
  public function updateCart();

  /**
   * Creates a cart for storage.
   */
  public function createCart();

  /**
   * Associate the current cart in storage with a given customer.
   */
  public function associateCart($customer_id);

}
