<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_acm\CartHelper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\Core\Cache\Cache;
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
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              APIWrapper $api_wrapper,
                              CartStorageInterface $cart_storage,
                              OrdersManager $orders_manager,
                              CartHelper $cart_helper,
                              RequestStack $request_stack,
                              AccountProxyInterface $current_user,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->apiWrapper = $api_wrapper;
    $this->cartStorage = $cart_storage;
    $this->ordersManager = $orders_manager;
    $this->cartHelper = $cart_helper;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->currentUser = $current_user;
    $this->logger = $logger_factory->get('alshaya_acm_checkout');
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
      Cache::invalidateTags(['cart_' . $cart->id()]);

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

      // Add success message in logs.
      $this->logger->info('Placed order. Cart: @cart.', [
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

}
