<?php

namespace Drupal\alshaya_knet\Helper;

use Drupal\alshaya_knet\E24PaymentPipe;
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
   * @param int|string $cart_id
   *   Cart ID.
   * @param int|string $current_user_id
   *   Current Drupal user id.
   * @param string $amount
   *   Cart Amount.
   * @param int|string $customer_id
   *   MDC Customer id.
   * @param string $order_id
   *   Reserved order id.
   * @param string $context
   *   Source of request - drupal/mobile.
   *
   * @return array
   *   Array containing url and state key.
   */
  public function initKnetRequest($cart_id,
                                  $current_user_id,
                                  $amount,
                                  $customer_id = NULL,
                                  $order_id = NULL,
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
    $this->state()->delete($state_key);
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

}
