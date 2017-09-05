<?php

namespace Drupal\alshaya_acm_knet\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_acm_checkout\CheckoutHelper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
   * Alshaya API Wrapper object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $alshayaApiWrapper;

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
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshaya_api_wrapper
   *   Alshaya API wrapper object.
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
                              AlshayaApiWrapper $alshaya_api_wrapper,
                              ConfigFactoryInterface $config_factory,
                              OrdersManager $orders_manager,
                              CartStorageInterface $cart_storage,
                              CheckoutHelper $checkout_helper,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->alshayaApiWrapper = $alshaya_api_wrapper;
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
      $container->get('alshaya_api.api'),
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
    $response['payment_id'] = $_POST['paymentid'];
    $response['result'] = $_POST['result'];
    $response['post_date'] = $_POST['postdate'];
    $response['transaction_id'] = $_POST['tranid'];
    $response['auth'] = $_POST['auth'];
    $response['ref'] = $_POST['ref'];
    $response['tracking_id'] = $_POST['trackid'];
    $response['user_id'] = $_POST['udf1'];
    $response['customer_id'] = $_POST['udf2'];
    $response['quote_id'] = $_POST['udf3'];
    $response['email'] = $_POST['udf4'];

    // Get the cart using API to validate.
    $cart = $this->apiWrapper->getCart($response['quote_id']);

    if (empty($cart)) {
      $this->logger->error('Invalid KNET response call found.<br>@message', [
        '@message' => json_encode($response),
      ]);

      exit;
    }

    $state_key = md5($response['quote_id']);
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

      $this->logger->info('KNET update for @quote_id: @result_url @message', [
        '@quote_id' => $response['order_id'],
        '@result_url' => $result_url,
        '@message' => json_encode($response),
      ]);
    }
    else {
      $result_url .= Url::fromRoute('alshaya_acm_knet.error', ['state_key' => $state_key], $url_options)->toString();

      $this->logger->error('KNET update for @quote_id: @result_url @message', [
        '@quote_id' => $response['quote_id'],
        '@result_url' => $result_url,
        '@message' => json_encode($response),
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
      throw new AccessDeniedHttpException();
    }

    // Place order now.
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart) || $cart->id() != $data['quote_id']) {
      throw new AccessDeniedHttpException();
    }

    $this->logger->info('KNET payment complete for @quote_id.<br>@message', [
      '@quote_id' => $data['quote_id'],
      '@message' => json_encode($data),
    ]);

    try {
      // Push the additional data to cart.
      $cart->setPaymentMethod('knet', $data);
      $updated_cart = $this->cartStorage->updateCart();

      // Place the order now.
      $this->checkoutHelper->placeOrder($updated_cart);

      // Delete the data from DB (state).
      \Drupal::state()->delete($state_key);
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      return $this->redirect('acq_checkout.form', ['step' => 'payment']);
    }

    return $this->redirect('acq_checkout.form', ['step' => 'confirmation']);
  }

  /**
   * Page callback for error state.
   */
  public function error($quote_id) {
    $cart = $this->cartStorage->getCart(FALSE);

    if ($cart->id() != $quote_id) {
      return new AccessDeniedHttpException();
    }

    $message = $this->t('Something went wrong, we dont have proper error message.');

    $message .= PHP_EOL . $this->t('Debug info:') . PHP_EOL;
    foreach ($_GET as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->logger->error('KNET payment failed for @quote_id: @message', [
      '@order_id' => $quote_id,
      '@message' => $message,
    ]);

    // @TODO: Confirm message.
    drupal_set_message($this->t('Sorry, we are unable to process your payment. Please try again with different method or contact our customer service team for assistance.'), 'error');

    return $this->redirect('acq_checkout.form', ['step' => 'payment']);
  }

  /**
   * Page callback for internal error state.
   */
  public function internalError($state_key) {
    $data = \Drupal::state()->get($state_key);

    if (empty($data)) {
      return new AccessDeniedHttpException();
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

    // @TODO: Confirm message.
    drupal_set_message($this->t('Sorry, we are unable to process your payment. Please try again with different method or contact our customer service team for assistance.'), 'error');

    return $this->redirect('acq_checkout.form', ['step' => 'payment']);
  }

}
