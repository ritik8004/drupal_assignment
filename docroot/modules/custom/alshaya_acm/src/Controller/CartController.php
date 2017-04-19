<?php

namespace Drupal\alshaya_acm\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acq_cart\CartStorageInterface;

/**
 * Class CartController.
 */
class CartController extends ControllerBase {

  /**
   * Drupal\acq_cart\CartStorageInterface definition.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Drupal\acq_cart\Cart definition.
   *
   * @var \Drupal\acq_cart\Cart
   */
  protected $cart;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart storage.
   */
  public function __construct(CartStorageInterface $cart_storage) {
    $this->cartStorage = $cart_storage;
    $this->cart = $this->cartStorage->getCart();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_cart.cart_storage')
    );
  }

  /**
   * Handler for cart/remove/{sku}.
   */
  public function cartRemoveSku($sku) {
    if (!empty($sku)) {
      // Remove the item from cart.
      $this->cart->removeItemFromCart($sku);
      // Update cart, after the item has been removed.
      $this->cartStorage->updateCart();

      drupal_set_message($this->t('<span>Item %sku has been removed from cart.', ['%sku' => $sku]), 'status</span>');
    }
    else {
      drupal_set_message($this->t('<span>Oops, something went wrong.'), 'error</span>');
    }
    return $this->redirect('acq_cart.cart');
  }

}
