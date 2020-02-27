<?php

namespace Drupal\acq_cart;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
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
   * API Wrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  private $apiWrapper;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(SessionInterface $session, APIWrapper $api_wrapper, LoggerChannelFactoryInterface $logger_factory) {
    $this->session = $session;
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('acq_cart');
  }

  /**
   * {@inheritdoc}
   */
  public function getCartId($create_new = TRUE) {
    $cart = $this->session->get(self::STORAGE_KEY);

    if ($cart) {
      return $cart->id();
    }
    elseif ($create_new) {
      $cart = $this->createCart();
      return $cart->id();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function addCart(CartInterface $cart) {
    $this->session->set(self::STORAGE_KEY, $cart);
    // Update cookies cache in Drupal to use new one.
    \Drupal::request()->cookies->set('Drupal_visitor_acq_cart_id', $cart->id());
    user_cookie_save([
      'acq_cart_id' => $cart->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function restoreCart($cart_id) {
    try {
      // @TODO: Need to rethink about this and get it done in single API call.
      $cart = (object) $this->apiWrapper->getCart($cart_id);

      if ($cart) {
        $cart->cart_id = $cart_id;
        $cart = new Cart($cart);
        $this->addCart($cart);
      }

      return TRUE;
    }
    catch (\Exception $e) {
      $this->logger->warning('Error occurred while restoring cart id %cart_id: %message', [
        '%cart_id' => $cart_id,
        '%message' => $e->getMessage(),
      ]);

      $this->clearCart();

      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearCart() {
    // Set header to clear.
    user_cookie_save([
      'acq_cart_id' => NULL,
    ]);

    // Clear in drupal request for next calls in same page load.
    \Drupal::request()->cookies->set('Drupal_visitor_acq_cart_id', NULL);

    // Clear the values in session.
    $this->session->remove(self::STORAGE_KEY);
  }

  /**
   * {@inheritdoc}
   */
  public function clearShippingMethodSession() {
    $cart = $this->getCart(FALSE);
    $cart->clearShippingMethod();
    $this->addCart($cart);
  }

  /**
   * {@inheritdoc}
   */
  public function getCart($create_new = TRUE) {
    $cart = $this->session->get(self::STORAGE_KEY);

    // No cart in session, try to load an updated cart.
    if (!$cart) {
      try {
        $cart = $this->updateCart($create_new);
      }
      catch (\Exception $e) {
        // Intentionally suppressing the error here. This will happen when there
        // is no cart and still updateCart is called.
      }
    }

    return $cart;
  }

  /**
   * Get skus of current cart items.
   *
   * @return array
   *   Items in the current cart.
   */
  public function getCartSkus() {
    $items = $this->getCart()->items();
    if (empty($items)) {
      return [];
    }

    $skus = [];
    foreach ($items as $item) {
      $skus[] = $item['sku'];
    }

    return $skus;
  }

  /**
   * {@inheritdoc}
   */
  public function updateCart($create_new = TRUE) {
    $cart_id = $this->getCartId($create_new);
    if (empty($cart_id)) {
      if (!$create_new) {
        // Simply return null if request was to update existing cart only.
        return NULL;
      }

      // Throw exception if request was to create if not exists.
      throw new \Exception(acq_commerce_api_down_global_error_message());
    }

    $update = NULL;
    $cart = $this->session->get(self::STORAGE_KEY);

    if ($cart) {
      // If cart exists, derive update array and update cookie.
      user_cookie_save([
        'acq_cart_id' => $cart->id(),
      ]);
      $update = $cart->getCart();

      // Don't tell conductor our stored totals for no reason.
      if (isset($update->totals)) {
        unset($update->totals);
      }

      try {
        $cartObject = (object) $this->apiWrapper->updateCart($cart_id, $update);
      }
      catch (\Exception $e) {
        // Restore the cart only if exception is not related to API being down.
        if (!acq_commerce_is_exception_api_down_exception($e)) {
          $this->restoreCart($cart_id);
        }

        throw $e;
      }

      if (empty($cartObject)) {
        return;
      }

      $cartObject->cart_id = $cart_id;

      if ($cart) {
        $cart->updateCartObject($cartObject);
      }
      else {
        $cart = new Cart($cartObject);
      }

      Cache::invalidateTags(['cart:' . $cart_id]);

      $this->addCart($cart);

      return $cart;
    }

    return NULL;
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

    $cart_response = (object) $this->apiWrapper->updateCart($cart->id(), $update);

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

    // @TODO: It seems this customer_id is never used by Magento.
    // We may need to edit Magento code to associate the cart if customer_id is
    // given or use the associate endpoint.
    if (!\Drupal::currentUser()->isAnonymous()) {
      $customer_id = \Drupal::currentUser()->getAccount()->acq_customer_id;
    }

    $cart = (object) $this->apiWrapper->createCart($customer_id);

    $cart = new Cart($cart);
    $this->addCart($cart);
    return $cart;
  }

  /**
   * {@inheritdoc}
   */
  public function associateCart($customer_id, $customer_email = '') {
    // We first update the session cart.
    $cart = $this->session->get(self::STORAGE_KEY);
    if (!$cart) {
      return;
    }

    $data = [
      'cart_id' => $cart->id(),
      'customer_id' => $customer_id,
      'customer_email' => $customer_email,
    ];
    $cart->convertToCustomerCart($data);
    $this->session->set(self::STORAGE_KEY, $cart);

    try {
      // Then we notify the commerce backend about the association.
      $this->apiWrapper->associateCart($cart->id(), $cart->customerId());
    }
    catch (\Exception $e) {
      $this->restoreCart($cart->id());
      throw $e;
    }
  }

  /**
   * Helper function to clear stock cache of all items in cart.
   */
  public function clearCartItemsStockCache() {
    $items = $this->getCart()->items();

    if (empty($items)) {
      return;
    }

    foreach ($items as $item) {
      if ($sku_entity = SKU::loadFromSku($item['sku'])) {
        $sku_entity->refreshStock();
      }
    }
  }

}
