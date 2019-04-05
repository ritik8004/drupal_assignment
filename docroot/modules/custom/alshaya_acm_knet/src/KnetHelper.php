<?php

namespace Drupal\alshaya_acm_knet;

use Drupal\acq_commerce\Conductor\APIWrapperInterface;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class KnetHelper.
 *
 * @package Drupal\alshaya_acm_knet
 */
class KnetHelper {

  /**
   * ACM API Wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapperInterface
   */
  private $api;

  /**
   * Alshaya API Wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  private $alshayaApi;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * State API.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private $state;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * KnetHelper constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapperInterface $api_wrapper
   *   ACM API Wrapper.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshaya_api
   *   Alshaya API Wrapper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\State\StateInterface $state
   *   State API.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   */
  public function __construct(APIWrapperInterface $api_wrapper,
                              AlshayaApiWrapper $alshaya_api,
                              ConfigFactoryInterface $config_factory,
                              StateInterface $state,
                              LoggerChannelInterface $logger) {
    $this->api = $api_wrapper;
    $this->alshayaApi = $alshaya_api;
    $this->configFactory = $config_factory;
    $this->state = $state;
    $this->logger = $logger;
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
   * Initialise knet request.
   *
   * @param int|string $cart_id
   *   Cart ID.
   * @param int|string $current_user_id
   *   Current Drupal user id.
   * @param int|string $customer_id
   *   MDC Customer id.
   * @param string $order_id
   *   Reserved order id.
   * @param string $amount
   *   Cart Amount.
   * @param string $context
   *   Source of request - drupal/mobile.
   *
   * @return array
   *   Array containing url and state key.
   */
  public function initKnetRequest($cart_id,
                             $current_user_id,
                             $customer_id,
                             $order_id,
                             $amount,
                             string $context = 'drupal'): array {
    // We store the cart id as cart id here and change it to quote id in
    // response so no one can directly use the state key from URL in error
    // and use it for success page.
    $state_data = [
      'cart_id' => $cart_id,
      'order_id' => $order_id,
    ];

    // This is just to have the key unique for state data.
    $state_key = md5(json_encode($state_data));

    $knetSettings = $this->configFactory->get('alshaya_acm_knet.settings');

    $pipe = new E24PaymentPipe();

    $pipe->setCurrency($knetSettings->get('knet_currency_code'));
    $pipe->setLanguage($knetSettings->get('knet_language_code'));

    // Set resource path.
    $pipe->setResourcePath($knetSettings->get('resource_path'));

    // Set your alias name here.
    $pipe->setAlias($knetSettings->get('alias'));

    $https = (bool) $knetSettings->get('use_secure_response_url');
    $url_options = ['absolute' => TRUE, 'https' => $https];

    $response_url = Url::fromRoute('alshaya_acm_knet.response', [], $url_options)->toString();

    if ($context === 'drupal') {
      $error_url = Url::fromRoute('alshaya_acm_knet.error', ['quote_id' => $cart_id], $url_options)->toString();
    }
    else {
      $error_url = Url::fromRoute('alshaya_acm_knet.mobile_error', ['state_key' => $state_key], $url_options)->toString();
    }

    $pipe->setResponseUrl($response_url);
    $pipe->setErrorUrl($error_url);

    $pipe->setAmt($amount);
    $pipe->setTrackId($order_id);
    $pipe->setUdf1($current_user_id);
    $pipe->setUdf2($customer_id);
    $pipe->setUdf3($cart_id);

    $pipe->setUdf4($state_key);

    $udf5_prefix = $knetSettings->get('knet_udf5_prefix');
    $pipe->setUdf5($udf5_prefix . ' ' . $order_id);

    $pipe->performPaymentInitialization();

    // Check again once if there is any error.
    if ($error = $pipe->getErrorMsg()) {
      throw new \RuntimeException($error);
    }

    $this->logger->info('Payment info for quote id @quote_id is @payment_id. Reserved order id is @order_id. State key: @state_key', [
      '@order_id' => $order_id,
      '@quote_id' => $cart_id,
      '@payment_id' => $pipe->getPaymentId(),
      '@state_key' => $state_key,
    ]);

    $state_data['context'] = $context;
    $state_data['payment_id'] = $pipe->getPaymentId();
    $state_data['amount'] = $amount;

    // We store the data in state here to ensure we can use it back and avoid
    // security issues.
    $this->state->set($state_key, $state_data);

    return [
      'state_key' => $state_key,
      'url' => $pipe->getRedirectUrl(),
    ];
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
   * Get data for knet transaction from state key.
   *
   * @param string $state_key
   *   State key.
   *
   * @return array
   *   State Data.
   */
  public function getKnetStatus(string $state_key): array {
    $data = $this->state->get($state_key);

    if (empty($data)) {
      throw new NotFoundHttpException();
    }

    return $data;
  }

}
