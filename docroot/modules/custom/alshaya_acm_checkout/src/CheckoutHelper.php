<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_acm\CartHelper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class CheckoutHelper.
 *
 * @package Drupal\alshaya_acm_checkout
 */
class CheckoutHelper {

  /**
   * API Wrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * The cart storage service.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Orders manager service object.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * Cart Helper service object.
   *
   * @var \Drupal\alshaya_acm\CartHelper
   */
  protected $cartHelper;

  /**
   * Address Book Manager service object.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  protected $addressBookManager;

  /**
   * Current request object.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The current user making the request.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Entity Type Manager service object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Cache Backend service for storing history of user data in cart.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheCartHistory;

  /**
   * The date time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

  /**
   * CheckoutOptionsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage service.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders manager service object.
   * @param \Drupal\alshaya_acm\CartHelper $cart_helper
   *   Cart Helper service object.
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   Address Book Manager service object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_cart_history
   *   Cache Backend service for storing history of user data in cart.
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The date time service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              APIWrapper $api_wrapper,
                              CartStorageInterface $cart_storage,
                              OrdersManager $orders_manager,
                              CartHelper $cart_helper,
                              AlshayaAddressBookManager $address_book_manager,
                              RequestStack $request_stack,
                              AccountProxyInterface $current_user,
                              LoggerChannelFactoryInterface $logger_factory,
                              CacheBackendInterface $cache_cart_history,
                              TimeInterface $date_time) {
    $this->entityTypeManager = $entity_type_manager;
    $this->apiWrapper = $api_wrapper;
    $this->cartStorage = $cart_storage;
    $this->ordersManager = $orders_manager;
    $this->cartHelper = $cart_helper;
    $this->addressBookManager = $address_book_manager;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->currentUser = $current_user;
    $this->logger = $logger_factory->get('alshaya_acm_checkout');
    $this->cacheCartHistory = $cache_cart_history;
    $this->dateTime = $date_time;
  }

  /**
   * Helper function to place order and do activities after it.
   *
   * @param \Drupal\acq_cart\CartInterface $cart
   *   Cart object.
   *
   * @throws \Exception
   */
  public function placeOrder(CartInterface $cart) {
    if (empty($cart)) {
      throw new \Exception('Cannot place order for empty cart');
    }

    try {
      // Place an order.
      $response = $this->apiWrapper->placeOrder($cart->id());

      // Once we reach here, we clear cart related cache.
      Cache::invalidateTags(['cart:' . $cart->id()]);

      // @TODO: Remove the fix when we get the full order details.
      $order_id = str_replace('"', '', $response['order']['id']);

      $session = $this->currentRequest->getSession();
      $session->set('last_order_id', $order_id);

      $current_user_id = 0;

      // Clear orders list cache if user is logged in.
      if ($this->currentUser->isAnonymous() || !alshaya_acm_customer_is_customer($this->currentUser)) {
        // Store the email address of customer in session.
        $email = $cart->customerEmail();
        $session->set('email_order_' . $order_id, $email);
      }
      else {
        $email = $this->currentUser->getEmail();
        $current_user_id = $this->currentUser->id();

        // Update user's mobile number if empty.
        $account = $this->entityTypeManager->getStorage('user')->load($current_user_id);

        if (empty($account->get('field_mobile_number')->getString())) {
          $billing = $this->cartHelper->getBilling($cart);
          $account->get('field_mobile_number')->setValue($billing['telephone']);
          $account->save();
        }
      }

      $session->save();

      $this->ordersManager->clearOrderCache($email, $current_user_id);
      $this->ordersManager->clearLastOrderRelatedProductsCache();
      $this->clearCartShippingHistory($cart->id());

      // Add success message in logs.
      $this->logger->info('Placed order. Cart id: @cart_id. Order id: @order_id.', [
        '@cart_id' => $cart->id(),
        '@order_id' => $order_id,
      ]);

      // While debugging we log the whole cart object.
      $this->logger->debug('Placed order for cart: @cart', [
        '@cart' => json_encode($cart->getCart()),
      ]);

      // Clear the cart in session.
      $this->cartStorage->clearCart();
    }
    catch (\Exception $e) {
      // Restore the cart.
      $this->cartStorage->restoreCart($cart->id());

      // Add message in logs.
      $this->logger->critical('Error occurred while placing order. Cart: @cart. Exception: @message', [
        '@cart' => json_encode($cart->getCart()),
        '@message' => $e->getMessage(),
      ]);

      // Throw the message for calling function too.
      throw $e;
    }
  }

  /**
   * Function to clear shipping info in cart and store current info in cache.
   *
   * @param string $current_method
   *   Current method in cart, we will store that code in cache.
   */
  public function clearShippingInfo($current_method) {
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      return;
    }

    if (empty($current_method)) {
      return;
    }

    $address = (array) $cart->getShipping();

    $extensions_to_remember = ['store_code', 'click_and_collect_type'];
    foreach ($extensions_to_remember as $code) {
      $extension[$code] = $cart->getExtension($code);
    }

    $this->setCartShippingHistory($current_method, $address, $extension);

    $empty_address = $this->addressBookManager->getAddressStructureWithEmptyValues('');

    // Clear address and info from extension.
    $cart->setShippingMethod('', '');
    $cart->setShipping($empty_address);
    $cart->setExtension('store_code', NULL);
    $cart->setExtension('click_and_collect_type', NULL);
    $cart->clearPayment();
    $this->cartStorage->updateCart();
  }

  /**
   * Get shipping info from cart history.
   *
   * @param string $method
   *   Method code (hd/cc). Empty value will return whole history.
   *
   * @return array
   *   History data if available or empty array.
   */
  public function getCartShippingHistory($method = '') {
    $cart = $this->cartStorage->getCart(FALSE);

    if (!empty($cart)) {
      // Get cache id.
      $cid = $this->getCartHistoryCacheId($cart->id());
      $history = $this->cacheCartHistory->get($cid);

      if ($history) {
        $data = $history->data;
        if (empty($method)) {
          return $data;
        }
        elseif (isset($data[$method])) {
          return $data[$method];
        }
      }
    }

    return [];
  }

  /**
   * Set shipping info in cache.
   *
   * @param string $method
   *   HD or CC. Method code.
   * @param array $address
   *   Magento address.
   * @param array $extension
   *   Values used from Cart extension.
   */
  public function setCartShippingHistory($method, array $address, array $extension = []) {
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      return;
    }

    // Get cache id.
    $cid = $this->getCartHistoryCacheId($cart->id());

    // Current history.
    $history = $this->getCartShippingHistory();

    // Prepare data to store in cache as history.
    // We will use it to restore in cart if user changes his mind again.
    $history[$method] = [
      'method' => $method,
      'address' => $address,
    ];

    foreach ($extension as $code => $value) {
      $history[$method][$code] = $value;
    }

    // We will remember only for an hour.
    $expire = $this->dateTime->getRequestTime() + 3600;
    $this->cacheCartHistory->set($cid, $history, $expire);
  }

  /**
   * Clear history for a particular cart.
   *
   * @param int $cart_id
   *   Cart ID to use to prepare Cache ID.
   */
  public function clearCartShippingHistory($cart_id) {
    $cid = $this->getCartHistoryCacheId($cart_id);
    $this->cacheCartHistory->delete($cid);
  }

  /**
   * Get Cache ID for history for a particular cart.
   *
   * @param int $cart_id
   *   Cart ID to use to prepare Cache ID.
   *
   * @return string
   *   Cache ID.
   */
  private function getCartHistoryCacheId($cart_id) {
    return 'cart_history:' . $cart_id;
  }

}
