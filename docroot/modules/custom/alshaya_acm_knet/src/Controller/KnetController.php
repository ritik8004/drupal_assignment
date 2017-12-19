<?php

namespace Drupal\alshaya_acm_knet\Controller;

use Drupal\acq_cart\Cart;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_acm_checkout\CheckoutHelper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CheckoutSettingsForm.
 */
class KnetController extends ControllerBase {

  /**
   * APIWrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * Drupal\acq_cart\CartStorageInterface definition.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Array containing knet settings from config.
   *
   * @var array
   */
  protected $knetSettings;

  /**
   * Orders Manager object.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * Checkout Helper service object.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutHelper
   */
  protected $checkoutHelper;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   API wrapper object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders Manager object.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   * @param \Drupal\alshaya_acm_checkout\CheckoutHelper $checkout_helper
   *   The cart session.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory object.
   */
  public function __construct(APIWrapper $api_wrapper,
                              ConfigFactoryInterface $config_factory,
                              OrdersManager $orders_manager,
                              CartStorageInterface $cart_storage,
                              CheckoutHelper $checkout_helper,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->knetSettings = $config_factory->get('alshaya_acm_knet.settings');
    $this->ordersManager = $orders_manager;
    $this->cartStorage = $cart_storage;
    $this->checkoutHelper = $checkout_helper;
    $this->logger = $logger_factory->get('alshaya_acm_knet');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_commerce.api'),
      $container->get('config.factory'),
      $container->get('alshaya_acm_customer.orders_manager'),
      $container->get('acq_cart.cart_storage'),
      $container->get('alshaya_acm_checkout.checkout_helper'),
      $container->get('logger.factory')
    );
  }

  /**
   * Page callback to process the payment and return redirect URL.
   */
  public function response() {
    $quote_id = isset($_POST['udf3']) ? $_POST['udf3'] : '';

    try {
      if (empty($quote_id)) {
        throw new \Exception();
      }

      // Get the cart using API to validate.
      $cart = (array) $this->apiWrapper->getCart($quote_id);

      if (empty($cart)) {
        throw new \Exception();
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Invalid KNET response call found.<br>POST: @message', [
        '@message' => json_encode($_POST),
      ]);

      throw new NotFoundHttpException();
    }

    $response['payment_id'] = $_POST['paymentid'];
    $response['result'] = $_POST['result'];
    $response['post_date'] = $_POST['postdate'];
    $response['transaction_id'] = $_POST['tranid'];
    $response['auth_code'] = $_POST['auth'];
    $response['ref'] = $_POST['ref'];
    $response['tracking_id'] = $_POST['trackid'];
    $response['user_id'] = $_POST['udf1'];
    $response['customer_id'] = $_POST['udf2'];
    $response['quote_id'] = $_POST['udf3'];
    $response['email'] = $_POST['udf4'];
    $state_key = $_POST['udf5'];

    $state_data = \Drupal::state()->get($state_key);

    // Check if we have data in state available and it matches data in POST.
    if (empty($state_data)
      || $state_data['cart_id'] != $response['quote_id']
      || $state_data['order_id'] != $response['tracking_id']
      || $state_data['payment_id'] != $response['payment_id']
    ) {
      $this->logger->error('KNET response data dont match data in state variable.<br>POST: @message<br>Cart: @cart<br>State: @state', [
        '@message' => json_encode($_POST),
        '@state' => json_encode($state_data),
        '@cart' => json_encode($cart->getCart()),
      ]);

      throw new NotFoundHttpException();
    }

    $totals = $cart['totals'];
    if ($state_data['amount'] != $totals['grand']) {
      $this->logger->error('Amount currently in cart dont match amount in state variable.<br>POST: @message<br>Cart: @cart<br>State: @state', [
        '@message' => json_encode($_POST),
        '@state' => json_encode($state_data),
        '@cart' => json_encode($cart),
      ]);

      throw new AccessDeniedHttpException();
    }

    // Store amount in state variable for logs.
    $response['amount'] = $totals['grand'];

    \Drupal::state()->set($state_key, $response);

    // On local/dev we don't use https for response url.
    // But for sure we want to use httpd on success url.
    $url_options = [
      'https' => TRUE,
      'absolute' => TRUE,
    ];

    $result_url = 'REDIRECT=';

    if ($response['result'] == 'CAPTURED') {
      $result_url .= Url::fromRoute('alshaya_acm_knet.success', ['state_key' => $state_key], $url_options)->toString();

      $this->logger->info('KNET update for @quote_id: Redirect: @result_url Response: @message Cart: @cart State: @state', [
        '@quote_id' => $response['quote_id'],
        '@result_url' => $result_url,
        '@message' => json_encode($response),
        '@state' => json_encode($state_data),
        '@cart' => json_encode($cart),
      ]);
    }
    else {
      $result_url .= Url::fromRoute('alshaya_acm_knet.failed', ['state_key' => $state_key], $url_options)->toString();

      $this->logger->info('KNET update for @quote_id: Redirect: @result_url Response: @message Cart: @cart State: @state', [
        '@quote_id' => $response['quote_id'],
        '@result_url' => $result_url,
        '@message' => json_encode($response),
        '@state' => json_encode($state_data),
        '@cart' => json_encode($cart),
      ]);
    }

    print $result_url;
    exit;
  }

  /**
   * Page callback for success state.
   */
  public function success($state_key) {
    $data = \Drupal::state()->get($state_key);

    if (empty($data)) {
      $this->logger->warning('KNET success page requested with invalid state_key: @state_key', [
        '@state_key' => $state_key,
      ]);

      throw new AccessDeniedHttpException();
    }

    // Get the cart from session.
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      $this->logger->warning('Cart not found in session but since payment was completed for @quote_id we restored it from Magento.', [
        '@quote_id' => $data['quote_id'],
        '@message' => json_encode($data),
      ]);

      try {
        $cartObject = (object) $this->apiWrapper->getCart($data['quote_id']);
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
      return $this->failed($state_key);
    }

    $this->logger->info('KNET payment complete for @quote_id.<br>@message', [
      '@quote_id' => $data['quote_id'],
      '@message' => json_encode($data),
    ]);

    try {
      // Push the additional data to cart.
      $cart->setPaymentMethod('knet', $data);
      $updatedCartObject = (object) $this->apiWrapper->updateCart($cart->id(), $cart->getCart());
      $cartObject->cart_id = $cart->id();
      $cart->updateCartObject($updatedCartObject);

      // Place the order now.
      if (isset($restored_cart) && $restored_cart) {
        $this->apiWrapper->placeOrder($cart->id());

        // Add success message in logs.
        $this->logger->info('Placed order. Cart: @cart.', [
          '@cart' => json_encode($updatedCartObject),
        ]);
      }
      else {
        $this->checkoutHelper->placeOrder($cart);
      }

      // Delete the data from DB (state).
      \Drupal::state()->delete($state_key);
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      return $this->redirect('acq_checkout.form', ['step' => 'payment']);
    }

    if (isset($restored_cart) && $restored_cart) {
      // We don't show the order if session not working.
      // User will get email.
      throw new AccessDeniedHttpException();
    }

    return $this->redirect('acq_checkout.form', ['step' => 'confirmation']);
  }

  /**
   * Page callback for error state.
   */
  public function error($quote_id) {
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
        '@cart' => json_encode($cart),
      ]);

      throw new AccessDeniedHttpException();
    }

    $message = $this->t('User either cancelled or response url returned error.');

    $message .= PHP_EOL . $this->t('Debug info:') . PHP_EOL;
    foreach ($_GET as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->logger->error('KNET payment failed for @quote_id: @message', [
      '@quote_id' => $quote_id,
      '@message' => $message,
    ]);

    // @TODO: Confirm message.
    drupal_set_message($this->t('Sorry, we are unable to process your payment. Please try again with different method or contact our customer service team for assistance.'), 'error');

    return $this->redirect('acq_checkout.form', ['step' => 'payment']);
  }

  /**
   * Page callback for failed state.
   */
  public function failed($state_key) {
    $data = \Drupal::state()->get($state_key);

    if (empty($data)) {
      $this->logger->warning('KNET failed page requested with invalid state_key: @state_key', [
        '@state_key' => $state_key,
      ]);

      throw new AccessDeniedHttpException();
    }

    $message = '';

    foreach ($data as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->logger->error('KNET payment failed for @quote_id: @message', [
      '@quote_id' => $data['quote_id'],
      '@message' => $message,
    ]);

    // Delete the data from DB.
    \Drupal::state()->delete($state_key);

    drupal_set_message($this->t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.'), 'error');

    return $this->redirect('acq_checkout.form', ['step' => 'payment']);
  }

}
