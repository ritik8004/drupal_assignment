<?php

namespace Drupal\alshaya_acm_knet;

use Drupal\acq_cart\Cart;
use Drupal\acq_commerce\Conductor\APIWrapperInterface;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_knet\Helper\KnetHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\alshaya_acm_checkout\CheckoutHelper;
use Drupal\alshaya_acm\CartHelper;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class AlshayaAcmKnetHelper.
 *
 * @package Drupal\alshaya_acm_knet
 */
class AlshayaAcmKnetHelper extends KnetHelper {

  use MessengerTrait;

  /**
   * K-Net Helper class.
   *
   * @var \Drupal\alshaya_knet\Helper\KnetHelper
   */
  protected $knetHelper;

  /**
   * ACM API Wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapperInterface
   */
  protected $api;

  /**
   * Alshaya API Wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $alshayaApi;

  /**
   * Drupal\acq_cart\CartStorageInterface definition.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Checkout Helper service object.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutHelper
   */
  protected $checkoutHelper;

  /**
   * Cart Helper service object.
   *
   * @var \Drupal\alshaya_acm\CartHelper
   */
  protected $cartHelper;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaAcmKnetHelper constructor.
   *
   * @param \Drupal\alshaya_knet\Helper\KnetHelper $knet_helper
   *   K-Net helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\TempStore\SharedTempStoreFactory $temp_store_factory
   *   The factory for the temp store object.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger channel.
   * @param \Drupal\acq_commerce\Conductor\APIWrapperInterface $api_wrapper
   *   ACM API Wrapper.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshaya_api
   *   Alshaya API Wrapper.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart storage.
   * @param \Drupal\alshaya_acm_checkout\CheckoutHelper $checkout_helper
   *   Checkout helper.
   * @param \Drupal\alshaya_acm\CartHelper $cart_helper
   *   Cart helper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(
    KnetHelper $knet_helper,
    ConfigFactoryInterface $config_factory,
    SharedTempStoreFactory $temp_store_factory,
    LoggerChannelInterface $logger,
    APIWrapperInterface $api_wrapper,
    AlshayaApiWrapper $alshaya_api,
    CartStorageInterface $cart_storage,
    CheckoutHelper $checkout_helper,
    CartHelper $cart_helper,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($config_factory, $temp_store_factory, $logger);
    $this->knetHelper = $knet_helper;
    $this->api = $api_wrapper;
    $this->alshayaApi = $alshaya_api;
    $this->cartStorage = $cart_storage;
    $this->checkoutHelper = $checkout_helper;
    $this->cartHelper = $cart_helper;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Validate if cart is good enough to initiate knet request.
   *
   * @param int $cart_id
   *   Cart ID.
   *
   * @return bool
   *   TRUE if cart is good enough.
   */
  public function validateCart(int $cart_id): bool {
    try {
      // Check if payment method is set to knet.
      $method = $this->alshayaApi->getCartPaymentMethod($cart_id);

      if ($method !== 'knet') {
        return FALSE;
      }

      // Check if order total is > 0.
      $cart = $this->getCart($cart_id);
      if ($cart['totals']['grand'] <= 0) {
        return FALSE;
      }

      return TRUE;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * Get cart data directly from MDC.
   *
   * @param int $cart_id
   *   Cart id.
   *
   * @return array
   *   Cart data.
   */
  public function getCart(int $cart_id): array {
    static $carts = [];

    if (isset($carts[$cart_id])) {
      return $carts[$cart_id];
    }

    try {
      $carts[$cart_id] = $this->api->getCart($cart_id);
    }
    catch (\Exception $e) {
      // If Cart not found or APIs not working, return empty array.
      return [];
    }

    return $carts[$cart_id];
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetResponse(array $response = []) {
    // Get the cart using API to validate.
    $cart = (array) $this->api->getCart($response['quote_id']);
    if (empty($cart)) {
      throw new \Exception();
    }

    $state_key = $response['state_key'];
    $state_data = $this->tempStore->get($state_key);
    $cartToLog = $this->cartHelper->getCleanCartToLog($cart);
    // Check if we have data in state available and it matches data in POST.
    if (empty($state_data)
      || $state_data['cart_id'] != $response['quote_id']
      || $state_data['order_id'] != $response['tracking_id']
      || $state_data['payment_id'] != $response['payment_id']
    ) {
      $this->logger->error('KNET response data dont match data in state variable.<br>POST: @message<br>Cart: @cart<br>State: @state', [
        '@message' => json_encode($_POST),
        '@state' => json_encode($state_data),
        '@cart' => $cartToLog,
      ]);
      throw new AccessDeniedHttpException();
    }
    $totals = $cart['totals'];
    if ($state_data['amount'] != $totals['grand']) {
      $this->logger->error('Amount currently in cart dont match amount in state variable.<br>POST: @message<br>Cart: @cart<br>State: @state', [
        '@message' => json_encode($_POST),
        '@state' => json_encode($state_data),
        '@cart' => $cartToLog,
      ]);
      throw new AccessDeniedHttpException();
    }
    // Store amount in state variable for logs.
    $response['amount'] = $totals['grand'];
    $this->tempStore->set($state_key, $response);
    // On local/dev we don't use https for response url.
    // But for sure we want to use httpd on success url.
    $url_options = [
      'https' => TRUE,
      'absolute' => TRUE,
    ];
    $result_url = 'REDIRECT=';
    if ($response['result'] == 'CAPTURED') {
      $route = 'alshaya_knet.success';
    }
    else {
      $route = 'alshaya_knet.failed';
    }

    // Allow other modules to alter success/fail route.
    $this->moduleHandler->alter('alshaya_knet_success_route', $route, $state_data);

    $redirect_url = Url::fromRoute($route, ['state_key' => $state_key], $url_options)->toString();
    $result_url .= $redirect_url;
    $this->logger->info('KNET update for @quote_id: Redirect: @result_url Response: @message Cart: @cart State: @state', [
      '@quote_id' => $response['quote_id'],
      '@result_url' => $result_url,
      '@message' => json_encode($response),
      '@state' => json_encode($state_data),
      '@cart' => $cartToLog,
    ]);

    // For new K-Net toolkit, we need to redirect.
    if ($this->knetHelper->useNewKnetToolKit()) {
      return new RedirectResponse($redirect_url, 302);
    }
    else {
      print $result_url;
      exit;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetSuccess(string $state_key, array $data = []) {
    // Get the cart from session.
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      $this->logger->warning('Cart not found in session but since payment was completed for @quote_id we restored it from Magento.', [
        '@quote_id' => $data['quote_id'],
        '@message' => json_encode($data),
      ]);

      try {
        $cartObject = (object) $this->api->getCart($data['quote_id']);
        $cart = new Cart($cartObject);
        $restored_cart = TRUE;
      }
      catch (\Exception $e) {
        $cart = NULL;
      }

      if (empty($cart) || $cart->id() != $data['quote_id']) {
        $this->logger->warning('KNET success page requested with valid state_key: @state_key but cart not found. State Data: @data', [
          '@state_key' => $state_key,
          '@data' => json_encode($data),
        ]);

        throw new AccessDeniedHttpException();
      }

    }

    // Additional check to ensure nobody copies the state key in url and loads
    // success page instead of error.
    if ($data['result'] !== 'CAPTURED') {
      return $this->processKnetFailed($state_key);
    }

    $this->logger->info('KNET payment complete for @quote_id.<br>@message', [
      '@quote_id' => $data['quote_id'],
      '@message' => json_encode($data),
    ]);

    try {
      // Push the additional data to cart.
      $this->checkoutHelper->setSelectedPayment('knet', $data, FALSE);
      $updatedCartObject = (object) $this->api->updateCart($cart->id(), $cart->getCart());
      $cart->updateCartObject($updatedCartObject);

      // Place the order now.
      if (isset($restored_cart) && $restored_cart) {
        $this->api->placeOrder($cart->id());

        // Add success message in logs.
        $this->logger->info('Placed order. Cart: @cart. Payment method @method.', [
          '@cart' => $this->cartHelper->getCleanCartToLog($cart),
          '@method' => 'knet',
        ]);
      }
      else {
        $this->checkoutHelper->placeOrder($cart);
      }

      // Preserve the payment id in a state variable to render it on Order
      // Confirmation page.
      $this->tempStore->set('knet:' . md5($cart->getExtension('real_reserved_order_id')), [
        'payment_id' => $data['payment_id'],
        'transaction_id' => $data['transaction_id'],
        'result_code' => $data['result'],
      ]);

      // Delete the data from DB (state).
      $this->tempStore->delete($state_key);
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      $url = Url::fromRoute('acq_checkout.form', ['step' => 'payment'])->toString();
      return new RedirectResponse($url, 302);
    }

    if (isset($restored_cart) && $restored_cart) {
      // We don't show the order if session not working.
      // User will get email.
      throw new AccessDeniedHttpException();
    }

    $url = Url::fromRoute('acq_checkout.form', ['step' => 'confirmation'])->toString();
    return new RedirectResponse($url, 302);
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetFailed(string $state_key) {
    $data = $this->tempStore->get($state_key);
    parent::processKnetFailed($state_key);

    $message = $this->t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.</br> Transaction ID: @transaction_id Payment ID: @payment_id Result code: @result_code', [
      '@transaction_id' => !empty($data['transaction_id']) ? $data['transaction_id'] : $data['quote_id'],
      '@payment_id' => $data['payment_id'],
      '@result_code' => $data['result'],
    ]);
    $this->messenger()->addError($message);

    $this->cartHelper->cancelCartReservation((string) $message);

    $url = Url::fromRoute('acq_checkout.form', ['step' => 'payment'])->toString();
    return new RedirectResponse($url, 302);
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetError(string $quote_id) {
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      $this->logger->warning('Empty cart for quote_id: @quote_id.', [
        '@quote_id' => $quote_id,
      ]);

      throw new AccessDeniedHttpException();
    }

    if ($cart->id() != $quote_id) {
      $this->logger->warning('KNET error page requested with invalid quote_id: @quote_id. Cart in session: @cart', [
        '@quote_id' => $quote_id,
        '@cart' => $this->cartHelper->getCleanCartToLog($cart),
      ]);

      throw new AccessDeniedHttpException();
    }

    // Log messages always in English.
    $message = 'User either cancelled or response url returned error.';
    $message .= PHP_EOL . 'Debug info:' . PHP_EOL;
    foreach ($_GET as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->logger->error('KNET payment failed for @quote_id: @message', [
      '@quote_id' => $quote_id,
      '@message' => $message,
    ]);

    $this->cartHelper->cancelCartReservation($message);

    // Get state data from cart & order id. Use same logic used for generating
    // the state key while initiating the knet payment.
    $state_data = [
      'cart_id' => $cart->id(),
      'order_id' => $cart->getExtension('real_reserved_order_id'),
    ];

    $state_key = md5(json_encode($state_data));
    $data = $this->tempStore->get($state_key);

    // @TODO: Confirm message.
    drupal_set_message($this->t('Sorry, we are unable to process your payment. Please try again with different method or contact our customer service team for assistance.</br> Transaction ID: @transaction_id Payment ID: @payment_id Result code: @result_code', [
      '@transaction_id' => !empty($data['transaction_id']) ? $data['transaction_id'] : $quote_id,
      '@payment_id' => $data['payment_id'],
      '@result_code' => $data['result'],
    ]), 'error');

    $url = Url::fromRoute('acq_checkout.form', ['step' => 'payment'])->toString();
    return new RedirectResponse($url, 302);
  }

}
