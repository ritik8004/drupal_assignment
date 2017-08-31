<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\user\Entity\User;
use Drupal\user\PrivateTempStoreFactory;

/**
 * Class CheckoutHelper.
 *
 * @package Drupal\alshaya_acm_checkout
 */
class CheckoutHelper {

  /**
   * The private temp store service.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $privateTempStore;

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
   * @param \Drupal\user\PrivateTempStoreFactory $private_temp_store
   *   Private temp store service.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage service.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders manager service object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(PrivateTempStoreFactory $private_temp_store,
                              APIWrapper $api_wrapper,
                              CartStorageInterface $cart_storage,
                              OrdersManager $orders_manager,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->privateTempStore = $private_temp_store;
    $this->apiWrapper = $api_wrapper;
    $this->cartStorage = $cart_storage;
    $this->ordersManager = $orders_manager;
    $this->logger = $logger_factory->get('alshaya_acm_checkout');
  }

  /**
   * Helper function to place order and do activities after it.
   *
   * @throws \Exception
   */
  public function placeOrder() {
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      throw new \Exception('Cannot place order for empty cart');
    }

    // Place an order.
    $response = $this->apiWrapper->placeOrder($cart->id());

    // Store the order details from response in tempstore.
    $temp_store = $this->privateTempStore->get('alshaya_acm_checkout');
    $temp_store->set('order', $response['order']);

    $current_user_id = 0;

    // Clear orders list cache if user is logged in.
    if (\Drupal::currentUser()->isAnonymous()) {
      // Store the email address of customer in tempstore.
      $email = $cart->customerEmail();
      $temp_store->set('email', $email);
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

    $this->ordersManager->clearOrderCache($email, $current_user_id);
    $this->ordersManager->clearLastOrderRelatedProductsCache();

    // Clear the cart in session.
    $this->cartStorage->clearCart();
  }

}
