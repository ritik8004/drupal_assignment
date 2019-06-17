<?php

namespace Drupal\alshaya_knet\Helper;

use Drupal\alshaya_knet\Knet\E24PaymentPipe;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\StateInterface;
use Drupal\alshaya_knet\Knet\KnetNewToolKit;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class KnetHelper.
 *
 * @package Drupal\alshaya_knet\Helper
 */
class KnetHelper {

  use StringTranslationTrait;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * State API.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Cart Id.
   *
   * @var mixed
   */
  protected $cartId = 0;

  /**
   * Current user id.
   *
   * @var mixed
   */
  protected $currentUserId = 0;

  /**
   * Customer id.
   *
   * @var mixed
   */
  protected $customerId = 0;

  /**
   * Order id.
   *
   * @var mixed
   */
  protected $orderId = NULL;

  /**
   * KnetHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\State\StateInterface $state
   *   State API.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              StateInterface $state,
                              LoggerChannelInterface $logger) {
    $this->configFactory = $config_factory;
    $this->state = $state;
    $this->logger = $logger;
  }

  /**
   * Initialize knet request.
   *
   * @param string $amount
   *   Cart Amount.
   * @param string $context
   *   Source of request - drupal/mobile.
   *
   * @return array
   *   Array containing url and state key.
   */
  public function initKnetRequest($amount,
                                  string $context = 'drupal'): array {
    $cart_id = $this->getCartId();
    $order_id = $this->getOrderId();
    // We store the cart id as cart id here and change it to quote id in
    // response so no one can directly use the state key from URL in error
    // and use it for success page.
    $state_data = [
      'cart_id' => $cart_id,
      'order_id' => $order_id,
    ];

    // This is just to have the key unique for state data.
    $state_key = md5(json_encode($state_data));

    $knetSettings = $this->configFactory->get('alshaya_knet.settings');

    // Get K-Net toolkit.
    $pipe = $this->getKnetToolKit();

    // If using new K-Net toolkit.
    if ($this->useNewKnetToolKit()) {
      // Get K-Net creds for new toolkit.
      $knet_creds = $this->getNewKnetToolkitCreds();
      $pipe->setTranportalId($knet_creds['tranportal_id']);
      $pipe->setTranportalPassword($knet_creds['tranportal_password']);
      $pipe->setTerminalResourceKey($knet_creds['terminal_resource_key']);
      $pipe->setKnetUrl($knet_creds['knet_url']);
    }

    $pipe->setCurrency($knetSettings->get('knet_currency_code'));
    $pipe->setLanguage($knetSettings->get('knet_language_code'));

    // Set resource path.
    $pipe->setResourcePath($knetSettings->get('resource_path'));

    // Set your alias name here.
    $pipe->setAlias($knetSettings->get('alias'));

    $https = (bool) $knetSettings->get('use_secure_response_url');
    $url_options = ['absolute' => TRUE, 'https' => $https];

    $response_url = Url::fromRoute('alshaya_knet.response', [], $url_options)->toString();

    if ($context === 'drupal') {
      $error_url = Url::fromRoute('alshaya_knet.error', ['quote_id' => $cart_id], $url_options)->toString();
    }
    else {
      $error_url = Url::fromRoute('alshaya_mobile_app.mobile_error', ['state_key' => $state_key], $url_options)->toString();
    }

    $pipe->setResponseUrl($response_url);
    $pipe->setErrorUrl($error_url);

    $pipe->setAmt($amount);
    $pipe->setTrackId($order_id);
    $pipe->setUdf1($this->getCurrentUserId());
    $pipe->setUdf2($this->getCustomerId());
    $pipe->setUdf3($cart_id);

    $pipe->setUdf4($state_key);

    $udf5_prefix = $knetSettings->get('knet_udf5_prefix');
    $pipe->setUdf5($udf5_prefix . ' ' . $order_id);

    $pipe->performPaymentInitialization();

    // Check again once if there is any error.
    if ($error = $pipe->getErrorMsg()) {
      throw new \RuntimeException($error);
    }

    $this->logger->info('Payment info for K-Net toolkit version:@version quote id is @quote_id. Reserved order id is @order_id. State key: @state_key', [
      '@order_id' => $order_id,
      '@quote_id' => $cart_id,
      '@payment_id' => $pipe->getPaymentId(),
      '@state_key' => $state_key,
      '@version' => $this->useNewKnetToolKit() ? 'v1' : 'v2',
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
   * Setter for cart id.
   *
   * @param mixed $cart_id
   *   Cart id.
   *
   * @return $this
   *   Current object.
   */
  public function setCartId($cart_id) {
    $this->cartId = $cart_id;
    return $this;
  }

  /**
   * Getter for cart id.
   *
   * @return mixed
   *   Cart id.
   */
  public function getCartId() {
    return $this->cartId;
  }

  /**
   * Setter for current user id.
   *
   * @param mixed $current_user_id
   *   Current user id.
   *
   * @return $this
   *   Current object.
   */
  public function setCurrentUserId($current_user_id) {
    $this->currentUserId = $current_user_id;
    return $this;
  }

  /**
   * Getter for current user id.
   *
   * @return mixed
   *   Current user id.
   */
  public function getCurrentUserId() {
    return $this->currentUserId;
  }

  /**
   * Setter for customer id.
   *
   * @param mixed $customer_id
   *   Customer id.
   *
   * @return $this
   *   Current object.
   */
  public function setCustomerId($customer_id) {
    $this->customerId = $customer_id;
    return $this;
  }

  /**
   * Getter for customer id.
   *
   * @return mixed
   *   Customer id.
   */
  public function getCustomerId() {
    return $this->customerId;
  }

  /**
   * Setter for order id.
   *
   * @param mixed $order_id
   *   Order id.
   *
   * @return $this
   *   Current object.
   */
  public function setOrderId($order_id) {
    $this->orderId = $order_id;
    return $this;
  }

  /**
   * Getter for order id.
   *
   * @return mixed
   *   Order id.
   */
  public function getOrderId() {
    return $this->orderId;
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

  /**
   * Process response of K-Net.
   *
   * @param array $response
   *   Response data.
   */
  public function processKnetResponse(array $response = []) {
    $state_key = $response['state_key'];
    $state_data = $this->state->get($state_key);
    $url_options = [
      'https' => TRUE,
      'absolute' => TRUE,
    ];
    if ($response['result'] == 'CAPTURED') {
      $route = 'alshaya_knet.success';
    }
    else {
      $route = 'alshaya_knet.failed';
    }
    $result_url = 'REDIRECT=';
    $result_url .= Url::fromRoute($route, ['state_key' => $state_key], $url_options)->toString();
    $this->logger->info('KNET update for @quote_id: Redirect: @result_url Response: @message State: @state', [
      '@quote_id' => $response['quote_id'],
      '@result_url' => $result_url,
      '@message' => json_encode($response),
      '@state' => json_encode($state_data),
    ]);
  }

  /**
   * Process success of K-Net.
   *
   * @param string $state_key
   *   State key.
   * @param array $data
   *   Data.
   *
   * @return mixed
   *   Response.
   */
  public function processKnetSuccess(string $state_key, array $data = []) {
    if ($data['result'] !== 'CAPTURED') {
      return $this->processKnetFailed($state_key);
    }

    $this->logger->info('KNET payment complete for @quote_id.<br>@message', [
      '@quote_id' => $data['quote_id'],
      '@message' => json_encode($data),
    ]);
  }

  /**
   * Processing of the K-Net failure.
   *
   * @param string $state_key
   *   State key.
   *
   * @return mixed
   *   Response.
   */
  public function processKnetFailed(string $state_key) {
    $data = $this->state->get($state_key);

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
    $this->state->delete($state_key);
  }

  /**
   * Processing of the K-Net error.
   *
   * @param string $quote_id
   *   Quote id.
   *
   * @return mixed
   *   Response.
   */
  public function processKnetError(string $quote_id) {
    $message = $this->t('User either cancelled or response url returned error.');

    $message .= PHP_EOL . $this->t('Debug info:') . PHP_EOL;
    foreach ($_GET as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->logger->error('KNET payment failed for @quote_id: @message', [
      '@quote_id' => $quote_id,
      '@message' => $message,
    ]);
  }

  /**
   * Determines if use of new K-Net toolkit.
   *
   * @return bool
   *   True if using new toolkit.
   */
  public function useNewKnetToolKit() {
    return $this->configFactory->get('alshaya_knet.settings')
      ->get('use_new_knet_toolkit');
  }

  /**
   * Get the K-Net toolkit object.
   *
   * @return \Drupal\alshaya_knet\Knet\E24PaymentPipe|\Drupal\alshaya_knet\Knet\KnetNewToolKit
   *   K-Net toolkit object.
   */
  public function getKnetToolKit() {
    if ($this->useNewKnetToolKit()) {
      return new KnetNewToolKit();
    }

    return new E24PaymentPipe();
  }

  /**
   * Get tranportal id, password and resource key for new K-Net toolkit.
   *
   * @return array
   *   Array of credentials.
   */
  public function getNewKnetToolkitCreds() {
    // @Todo: Need to discuss the way to store/safe these values.
    return [
      'tranportal_id' => '',
      'tranportal_password' => '',
      'terminal_resource_key' => '',
      'knet_url' => '',
    ];
  }

  /**
   * Parse and prepare K-Net response data for new toolkit.
   *
   * @param array $input
   *   Data to parse.
   *
   * @return array
   *   Data to return after parse.
   */
  public function parseAndPrepareKnetData(array $input) {
    // If error is available.
    if (!empty($input['ErrorText']) || !empty($input['Error'])) {
      return $input;
    }

    $en_dec = $this->getKnetToolKit();
    $terminal_resource_key = $this->getNewKnetToolkitCreds()['terminal_resource_key'];
    $output = [];
    // Decrypted data contains a string which seperates values by `&`, so we
    // need to explode this. Example - 'paymentId=123&amt=4545'.
    $decrypted_data = array_filter(explode('&', $en_dec->decrypt($input['trandata'], $terminal_resource_key)));
    array_walk($decrypted_data, function ($val, $key) use (&$output) {
      list($key, $value) = explode('=', $val);
      $output[$key] = $value;
    });

    return $output;
  }

}
