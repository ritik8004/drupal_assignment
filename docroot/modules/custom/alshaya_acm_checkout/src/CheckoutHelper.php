<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\acq_cart\Cart;
use Drupal\acq_cart\CartInterface;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_acm\CartHelper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\profile\Entity\Profile;
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

  protected $addressManager;

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
   * The factory for configuration objects.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage service.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders manager service object.
   * @param \Drupal\alshaya_acm\CartHelper $cart_helper
   *   Cart Helper service object.
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_manager
   *   Address Book Manager.
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
                              ConfigFactoryInterface $config_factory,
                              APIWrapper $api_wrapper,
                              CartStorageInterface $cart_storage,
                              OrdersManager $orders_manager,
                              CartHelper $cart_helper,
                              AlshayaAddressBookManager $address_manager,
                              RequestStack $request_stack,
                              AccountProxyInterface $current_user,
                              LoggerChannelFactoryInterface $logger_factory,
                              CacheBackendInterface $cache_cart_history,
                              TimeInterface $date_time) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->apiWrapper = $api_wrapper;
    $this->cartStorage = $cart_storage;
    $this->ordersManager = $orders_manager;
    $this->cartHelper = $cart_helper;
    $this->addressManager = $address_manager;
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
      if (isset($response['order_id'])) {
        $order_id = $response['order_id'];
      }
      elseif (isset($response['order']['id'])) {
        $order_id = str_replace('"', '', $response['order']['id']);
      }
      else {
        throw new \Exception('Place order returned success in response but order id not returned in response.');
      }

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
      $this->clearCartHistory($cart->id());

      // Customer type needs to be updated in GTM
      // if the customer is a repeat customer.
      if (alshaya_acm_customer_is_customer($this->currentUser) && count(alshaya_acm_customer_get_user_orders($email)) < 3) {
        // Add cookie to refresh user data in local storage for GTM.
        user_cookie_save(['alshaya_gtm_user_refresh' => 1]);
      }

      // Add success message in logs.
      $this->logger->info('Placed order. Cart id: @cart_id. Order id: @order_id. Payment method: @method', [
        '@cart_id' => $cart->id(),
        '@order_id' => $order_id,
        '@method' => $session->get('selected_payment_method'),
      ]);

      // While debugging we log the whole cart object.
      $this->logger->debug('Placed order for cart: @cart', [
        '@cart' => $this->cartHelper->getCleanCartToLog($cart),
      ]);

      // Clear the cart in session.
      $this->cartStorage->clearCart();
    }
    catch (\Exception $e) {
      // Restore the cart.
      $this->cartStorage->restoreCart($cart->id());

      // Add message in logs.
      $this->logger->critical('Error occurred while placing order. Cart: @cart. Exception: @message', [
        '@cart' => $this->cartHelper->getCleanCartToLog($cart),
        '@message' => $e->getMessage(),
      ]);

      // Throw the message for calling function too.
      throw $e;
    }
  }

  /**
   * Reset stock cache and Drupal cache of products in cart.
   *
   * @param \Drupal\acq_cart\CartInterface $cart
   *   Cart.
   */
  public function refreshStockForProductsInCart(CartInterface $cart) {
    $this->cartHelper->refreshStockForProductsInCart($cart);
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

    // Clear address and info from extension.
    $cart->setShippingMethod('', '');
    $cart->setExtension('store_code', NULL);
    $cart->setExtension('click_and_collect_type', NULL);
    $cart->clearPayment();
    $this->updateCartWrapper(__FUNCTION__);
  }

  /**
   * Get cart history.
   *
   * @param string $key
   *   Key for which we want data from cart history.
   *
   * @return array
   *   History data if available or empty array.
   */
  public function getCartHistory($key) {
    $cart = $this->cartStorage->getCart(FALSE);

    if ($cart instanceof Cart) {
      // Get cache id.
      $cid = $this->getCartHistoryCacheId($cart->id());
      $history = $this->cacheCartHistory->get($cid);

      if ($history) {
        return $history->data[$key] ?? [];
      }
    }

    return [];
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
    $history = $this->getCartHistory('shipping');

    if (!empty($history)) {
      if (empty($method)) {
        return $history;
      }
      elseif (isset($history[$method])) {
        return $history[$method];
      }
    }

    return [];
  }

  /**
   * Set cart data into cache.
   *
   * @param string $key
   *   Key in which data should be stored.
   * @param mixed $data
   *   Data to store in cache for particular key.
   */
  public function setCartHistory($key, $data) {
    $cart = $this->cartStorage->getCart(FALSE);

    if ($cart instanceof Cart) {
      // Get cache id.
      $cid = $this->getCartHistoryCacheId($cart->id());
      $cache = $this->cacheCartHistory->get($cid);
      $history = $cache->data ?? [];
      $history[$key] = $data;
      $this->cacheCartHistory->set($cid, $history);
    }
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
    // Current history.
    $history = $this->getCartHistory('shipping');

    $address = $this->cleanCheckoutAddress($address);

    // Prepare data to store in cache as history.
    // We will use it to restore in cart if user changes his mind again.
    $history[$method] = [
      'method' => $method,
      'address' => $address,
    ];

    foreach ($extension as $code => $value) {
      $history[$method][$code] = $value;
    }

    $this->setCartHistory('shipping', $history);
  }

  /**
   * Clear history for a particular cart.
   *
   * @param int $cart_id
   *   Cart ID to use to prepare Cache ID.
   */
  public function clearCartHistory($cart_id) {
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

  /**
   * Get selected payment info from cache.
   *
   * @param bool $full_details
   *   Flag to say if full details are required or just the name.
   *
   * @return array|string
   *   Array if full details are required, string otherwise.
   */
  public function getSelectedPayment($full_details = FALSE) {
    // Save the current selection into history.
    $history = $this->getCartHistory('payment');

    if ($history) {
      return $full_details ? $history : $history['method'];
    }

    return $full_details ? [] : '';
  }

  /**
   * Set selected payment into Cart and in History.
   *
   * @param string $method
   *   Payment method code.
   * @param array $data
   *   Payment additional data.
   * @param bool $push
   *   Push the updates to Magento if true.
   */
  public function setSelectedPayment($method, array $data = [], $push = TRUE) {
    $cart = $this->cartStorage->getCart(FALSE);
    $cart->setPaymentMethod($method, $data);

    if ($push) {
      $this->updateCartWrapper(__FUNCTION__);
    }

    // Save the current selection into cache.
    $history = [];
    if ($method) {
      $history = [
        'method'          => $method,
        'additional_data' => $data,
      ];
    }
    $this->setCartHistory('payment', $history);
  }

  /**
   * Clear payment info from cart.
   */
  public function clearPayment() {
    $cart = $this->cartStorage->getCart(FALSE);

    if ($cart instanceof Cart) {
      $cart->clearPayment();
      $this->setCartHistory('payment', NULL);
    }
  }

  /**
   * Function to set billing address into cart and cache.
   *
   * @param bool $is_same
   *   If billing is same as shipping or not.
   * @param array $address
   *   Magento address.
   */
  public function setBillingInfo($is_same, array $address = []) {
    $cart = $this->cartStorage->getCart(FALSE);

    if (!($cart instanceof Cart)) {
      return;
    }

    if ($is_same) {
      $address = $this->cartHelper->getShipping($cart);
    }

    $address = $this->cleanCheckoutAddress($address);

    $cart->setBilling($address);

    $data = [
      'is_same' => $is_same,
      'address' => $address,
    ];

    $this->setCartHistory('billing', $data);
  }

  /**
   * Get billing info from history.
   *
   * @return array
   *   Billing info.
   */
  public function getBillingInfoFromHistory() {
    return $this->getCartHistory('billing');
  }

  /**
   * Check is cod surcharge is enabled or not.
   *
   * @return bool
   *   TRUE if enabled.
   */
  public function isSurchargeEnabled() {
    return (bool) $this->configFactory->get('alshaya_acm_checkout.settings')->get('cod_surcharge_status');
  }

  /**
   * Set billing address from shipping.
   *
   * @param bool $override
   *   Override current billing address.
   */
  public function setBillingFromShipping($override = TRUE) {
    $cart = $this->cartStorage->getCart(FALSE);

    if ($cart instanceof Cart) {
      if (!$override) {
        // Check if we already have it, do nothing.
        $billing = $this->cartHelper->getBilling();
        if (!empty($billing)) {
          return;
        }
      }

      $this->setBillingInfo(TRUE);
    }
  }

  /**
   * Helper function to clean address array.
   *
   * @param mixed $address
   *   Address array or object.
   *
   * @return array
   *   Cleaned address array.
   */
  public function cleanCheckoutAddress($address) {
    $address = (array) $address;

    $allowed_fields = [
      'firstname',
      'first_name',
      'lastname',
      'last_name',
      'telephone',
      'street',
      'street2',
      'city',
      'region',
      'postcode',
      'country_id',
      'extension',
    ];

    foreach ($address as $key => $value) {
      if (!in_array($key, $allowed_fields)) {
        unset($address[$key]);
      }
    }

    if (!empty($address['region'])) {
      // TODO: We may just require region and not region_id, need to verify.
      $address['region_id'] = alshaya_acm_checkout_get_region_id_from_name($address['region'], $address['country_id']);
      $address['region'] = $address['region_id'];
    }

    if (!empty($address['telephone'])) {
      $address['telephone'] = _alshaya_acm_checkout_clean_address_phone($address['telephone']);
    }

    // City is Magento core field but we don't use it at all.
    // But this is required by Cybersource so we need proper value.
    // For now, we copy value of Area to City.
    $address['city'] = $this->addressManager->getAddressShippingAreaValue($address);

    return $address;
  }

  /**
   * Helper function to get full address from Profile.
   *
   * @param \Drupal\profile\Entity\Profile $entity
   *   Profile entity.
   *
   * @return array
   *   Full address.
   */
  public function getFullAddressFromEntity(Profile $entity) {
    $full_address = $this->addressManager->getAddressFromEntity($entity);
    $full_address = $this->cleanCheckoutAddress($full_address);

    // Use the estimate delivery method with full address every-time.
    // This is a hack to avoid issues with estimate shipping having customer id.
    unset($full_address['address_id']);
    unset($full_address['customer_address_id']);
    return $full_address;
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
    $this->cartHelper->updateCartWrapper($function);
  }

}
