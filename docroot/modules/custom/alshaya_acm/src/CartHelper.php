<?php

namespace Drupal\alshaya_acm;

use Drupal\acq_cart\Cart;
use Drupal\acq_cart\CartInterface;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Response\NeedsRedirectException;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * ApiHelper.
 */
class CartHelper {

  use MessengerTrait;
  use StringTranslationTrait;

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
   * @param \Drupal\acq_cart\Cart|object|array $cart
   *   Cart object to clean.
   *
   * @return string
   *   Cleaned cart data as JSON string.
   */
  public function getCleanCartToLog($cart) {
    $cartData = $cart instanceof Cart ? $cart->getCart() : $cart;

    if (is_object($cartData)) {
      $cartData = (array) $cartData;
    }

    $shipping = $this->getAddressArray($cartData['shipping']);

    // Billing is not required for debugging.
    unset($cartData['billing']);

    // We will remove all at root level.
    // We will leave fields in extension here.
    unset($cartData['shipping']);
    $cartData['shipping']['extension'] = $shipping['extension'];

    return json_encode($cartData);
  }

  /**
   * Remove out of stock items from cart (in session only).
   *
   * @return bool
   *   TRUE if any item removed.
   */
  public function removeOutOfStockItemsFromCart(): bool {
    $removed = FALSE;
    $cart = $this->cartStorage->getCart(FALSE);

    // Sanity check.
    if (empty($cart)) {
      return $removed;
    }

    $items = $cart->items();

    foreach ($items as $index => $item) {
      $sku = SKU::loadFromSku($item['sku']);
      $plugin = $sku->getPluginInstance();
      if (!$plugin->isProductInStock($sku)) {
        $removed = TRUE;
        unset($items[$index]);
      }
    }

    if ($removed) {
      $cart->setItemsInCart($items);
    }

    return $removed;
  }

  /**
   * Wrapper function to remove item from cart.
   *
   * Tries to remove all other OOS items as well if required.
   *
   * @param string $sku
   *   SKU to remove.
   *
   * @throws \Drupal\acq_commerce\Response\NeedsRedirectException
   */
  public function removeItemFromCart(string $sku) {
    $cart = $this->cartStorage->getCart(FALSE);
    $cart->removeItemFromCart($sku);

    try {
      $this->updateCartWrapper(__METHOD__);
    }
    catch (\Exception $e) {
      // Try to remove again (only once) after removing OOS items.
      if ($this->removeOutOfStockItemsFromCart()) {
        $cart = $this->cartStorage->getCart(FALSE);
        $cart->removeItemFromCart($sku);
        $this->updateCartWrapper(__METHOD__);

        // Operation was successful after second try, show the error message
        // for user to know about the updates user didn't ask for.
        $this->messenger()->addError($this->t('Sorry, one or more products in your basket are no longer available and have been removed in order to proceed.'));
      }
    }
  }

  /**
   * Wrapper function to update cart and handle exception.
   *
   * @param string $function
   *   Function name invoking update cart for logs.
   *
   * @throws \Drupal\acq_commerce\Response\NeedsRedirectException
   */
  public function updateCartWrapper(string $function) {
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      throw new NeedsRedirectException(Url::fromRoute('acq_cart.cart')->toString());
    }

    try {
      $this->cartStorage->updateCart(FALSE);
    }
    catch (\Exception $e) {
      $this->logger->error('Error while updating cart @cart_id, invoked from @function, exception: @message', [
        '@message' => $e->getMessage(),
        '@cart_id' => $cart->id(),
        '@function' => $function,
      ]);

      if (_alshaya_acm_is_out_of_stock_exception($e)) {
        if ($cart = $this->cartStorage->getCart(FALSE)) {
          $this->refreshStockForProductsInCart($cart);
          $cart->setCheckoutStep('');
        }
      }

      throw new NeedsRedirectException(Url::fromRoute('acq_cart.cart')->toString());
    }
  }

  /**
   * Refresh stock cache and Drupal cache of products in cart.
   *
   * @param \Drupal\acq_cart\CartInterface|null $cart
   *   Cart.
   */
  public function refreshStockForProductsInCart(CartInterface $cart = NULL) {
    if (empty($cart)) {
      $cart = $this->cartStorage->getCart(FALSE);
    }

    // Still if empty, simply return.
    if (empty($cart)) {
      return;
    }

    foreach ($cart->items() ?? [] as $item) {
      if ($sku_entity = SKU::loadFromSku($item['sku'])) {
        $sku_entity->refreshStock();
      }
    }
  }

}
