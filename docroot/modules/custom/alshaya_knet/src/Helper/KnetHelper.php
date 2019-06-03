<?php

namespace Drupal\alshaya_knet\Helper;

use Drupal\alshaya_knet\E24PaymentPipe;
use Drupal\alshaya_knet\Knet\KnetEncryptDecypt;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\StateInterface;
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

    $pipe = new E24PaymentPipe();

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

    //$pp = $this->testNewToolkitRequest();
    //$response = new \Symfony\Component\HttpFoundation\RedirectResponse($pp['url']);
    //$response->send();
    //exit;
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
   */
  public function useNewKnetToolKit() {
    return TRUE;
  }

  public function testNewToolkitRequest() {
    // This needs to know
    $TranportalId="IDHERE";
    $ReqTranportalId="id=".$TranportalId;

    // This needs to know
    $TranportalPassword='PASSWORD HERE';
    $ReqTranportalPassword="password=".$TranportalPassword;

    $TranAmount = 10;
    $ReqAmount="amt=".$TranAmount;

    $TranTrackid=mt_rand();
    $ReqTrackId="trackid=".$TranTrackid;

    $ReqCurrency="currencycode=414";

    $ReqLangid="langid=USA";

    $ReqAction="action=1";

    $ResponseUrl="https://local.alshaya-hmsa.com/en/knet/response";
    $ReqResponseUrl="responseURL=".$ResponseUrl;

    $ErrorUrl="https://local.alshaya-hmsa.com/en/knet/error/0";
    $ReqErrorUrl="errorURL=".$ErrorUrl;

    $ReqUdf1="udf1=test1";
    $ReqUdf2="udf2=test2";
    $ReqUdf3="udf3=test3";
    $ReqUdf4="udf4=test4";
    $ReqUdf5="udf5=test5";

    $param=$ReqTranportalId."&".$ReqTranportalPassword."&".$ReqAction."&".$ReqLangid."&".$ReqCurrency."&".$ReqAmount."&".$ReqResponseUrl."&".$ReqErrorUrl."&".$ReqTrackId."&".$ReqUdf1."&".$ReqUdf2."&".$ReqUdf3."&".$ReqUdf4."&".$ReqUdf5;

    $termResourceKey="";
    $enc_dec = new KnetEncryptDecypt();
    $param=$enc_dec->encryptAES($param,$termResourceKey)."&tranportalId=".$TranportalId."&responseURL=".$ResponseUrl."&errorURL=".$ErrorUrl;

    return [
      'url' => "https://kpaytest.com.kw/kpg/PaymentHTTP.htm?param=paymentInit"."&trandata=".$param,
    ];
  }

}
