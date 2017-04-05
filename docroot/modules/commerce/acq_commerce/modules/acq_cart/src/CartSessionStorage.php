<?php

/**
 * @file
 * Contains \Drupal\acq_cart\CartSessionStorage.
 */

namespace Drupal\acq_cart;

use Drupal\acq_cart\Cart;
use Drupal\acq_cart\CartStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class CartSessionStorage.
 *
 * @package Drupal\acq_cart
 */
class CartSessionStorage implements CartStorageInterface {

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   */
  public function __construct(SessionInterface $session) {
    $this->session = $session;
  }

  /**
   * {@inheritdoc}
   */
  public function getCartId() {
    $cookies = \Drupal::request()->cookies->all();
    $cart_id = NULL;

    if (isset($cookies['Drupal_visitor_acq_cart_id'])) {
      return $cookies['Drupal_visitor_acq_cart_id'];
    }

    $cart = $this->session->get(self::STORAGE_KEY);

    if ($cart) {
      return $cart->id();
    }

    $cart = $this->createCart();
    return $cart->id();
  }

  /**
   * {@inheritdoc}
   */
  public function addCart(CartInterface $cart) {
    $this->session->set(self::STORAGE_KEY, $cart);
    user_cookie_save([
      'acq_cart_id' => $cart->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCart() {
    $cart = $this->session->get(self::STORAGE_KEY);

    // No cart in session, try to load an updated cart.
    if (!$cart) {
      try {
        $cart = $this->updateCart();
      }
      catch (\Exception $e) {
        // Intentionally suppressing the error here. This will happen when there
        // is no cart and still updateCart is called.
      }
    }

    return $cart;
  }

  /**
   * {@inheritdoc}
   */
  public function updateCart() {
    $cart_id = $this->getCartId();
    $update = [];

    $cart = $this->session->get(self::STORAGE_KEY);

    // If cart exists, derive update array and update cookie.
    if ($cart) {
      user_cookie_save([
        'acq_cart_id' => $cart->id(),
      ]);
      $update = $cart->getCart();
    }

    $cart = (object) \Drupal::service('acq_commerce.api')->updateCart($cart_id, $update);

    if (empty($cart)) {
      return;
    }

    $cart->cart_id = $cart_id;
    $cart = new Cart($cart);
    $this->addCart($cart);
    return $cart;
  }

  /**
   * {@inheritdoc}
   */
  public function pushCart() {
    $cart = $this->session->get(self::STORAGE_KEY);

    // If cart exists, derive update array and update cookie.
    if ($cart) {
      user_cookie_save([
        'acq_cart_id' => $cart->id(),
      ]);
      $update = $cart->getCart();
    }

    $cart_response = (object) \Drupal::service('acq_commerce.api')->updateCart($cart->id(), $update);

    if (empty($cart_response)) {
      return;
    }

    return $cart;
  }

  /**
   * {@inheritdoc}
   */
  public function createCart() {
    $customer_id = NULL;

    if (!\Drupal::currentUser()->isAnonymous()) {
      $customer_id = \Drupal::currentUser()->getAccount()->acq_customer_id;
    }

    $cart = (object) \Drupal::service('acq_commerce.api')->createCart($customer_id);

    $cart = new Cart($cart);
    $this->addCart($cart);
    return $cart;
  }

}
