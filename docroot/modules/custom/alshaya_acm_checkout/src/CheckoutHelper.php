<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\user\Entity\User;

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
   * CheckoutOptionsManager constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage service.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders manager service object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(APIWrapper $api_wrapper,
                              CartStorageInterface $cart_storage,
                              OrdersManager $orders_manager,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->cartStorage = $cart_storage;
    $this->ordersManager = $orders_manager;
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

      $session = \Drupal::request()->getSession();
      $session->set('last_order_id', $order_id);

      $current_user_id = 0;

      // Clear orders list cache if user is logged in.
      if (\Drupal::currentUser()->isAnonymous() || !alshaya_acm_customer_is_customer(\Drupal::currentUser())) {
        // Store the email address of customer in session.
        $email = $cart->customerEmail();
        $session->set('email_order_' . $order_id, $email);
      }
      else {
        $email = \Drupal::currentUser()->getEmail();
        $current_user_id = \Drupal::currentUser()->id();

        // Update user's mobile number if empty.
        $account = User::load($current_user_id);

        if (empty($account->get('field_mobile_number')->getString())) {
          $billing = (array) $cart->getBilling();
          $account->get('field_mobile_number')->setValue($billing['telephone']);
          $account->save();
        }
      }

      $session->save();

      $this->ordersManager->clearOrderCache($email, $current_user_id);
      $this->ordersManager->clearLastOrderRelatedProductsCache();

      // Clear the cart in session.
      $this->cartStorage->clearCart();

      // Add success message in logs.
      $this->logger->info('Placed order. Cart: @cart.', [
        '@cart' => json_encode($cart),
      ]);
    }
    catch (\Exception $e) {
      // Add message in logs.
      $this->logger->critical('Error occurred while placing order. Cart: @cart. Exception: @message', [
        '@cart' => json_encode($cart),
        '@message' => $e->getMessage(),
      ]);

      // Throw the message for calling function too.
      throw $e;
    }
  }

}
