<?php

namespace Drupal\alshaya_acm;

use Drupal\acq_cart\Cart;
use Drupal\acq_cart\CartInterface;
use Drupal\acq_cart\CartStorageInterface;

/**
 * ApiHelper.
 */
class CartHelper {

  /**
   * The cart storage service.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage service.
   */
  public function __construct(CartStorageInterface $cart_storage) {
    $this->cartStorage = $cart_storage;
  }

  /**
   * Get the cart object.
   *
   * @return \Drupal\acq_cart\CartInterface
   *   The cart object.
   */
  public function getCart() {
    return $this->cartStorage->getCart(FALSE);
  }

  /**
   * Wrapper function to get cleaned shipping address.
   *
   * @param \Drupal\acq_cart\CartInterface|null $cart
   *   Cart object.
   *
   * @return array
   *   Payment methods.
   */
  public function getShipping(CartInterface $cart = NULL) {
    if (empty($cart)) {
      return [];
    }

    return $this->getAddressArray($cart->getShipping());
  }

  /**
   * Wrapper function to get cleaned billing address.
   *
   * @param \Drupal\acq_cart\CartInterface|null $cart
   *   Cart object.
   *
   * @return array
   *   Payment methods.
   */
  public function getBilling(CartInterface $cart = NULL) {
    if (empty($cart)) {
      return [];
    }

    return $this->getAddressArray($cart->getBilling());
  }

  /**
   * Get magento address as array.
   *
   * @param mixed $address
   *   Address object or array.
   *
   * @return array
   *   Processed address array.
   */
  public function getAddressArray($address) {
    // Convert this to array, we always deal with arrays in our custom code.
    if (is_object($address)) {
      $address = (array) $address;
    }

    // Empty check.
    if (empty($address['country_id'])) {
      return [];
    }

    // Convert extension too.
    if (isset($address['extension']) && is_object($address['extension'])) {
      $address['extension'] = (array) $address['extension'];
    }

    return $address;
  }

  /**
   * Get clean cart to log.
   *
   * @param \Drupal\acq_cart\Cart|object $cart
   *   Cart object to clean.
   *
   * @return string
   *   Cleaned cart data as JSON string.
   */
  public function getCleanCartToLog($cart) {
    $object = $cart instanceof Cart ? $cart->getCart() : $cart;
    $shipping = $this->getAddressArray($object->shipping);

    // Billing is not required for debugging.
    unset($object->billing);

    // We will remove all at root level.
    // We will leave fields in extension here.
    unset($object->shipping);
    $object->shipping['extension'] = $shipping['extension'];

    return json_encode($object);
  }

}
