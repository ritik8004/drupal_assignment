<?php

namespace Drupal\acq_checkoutcom;

use Acquia\Hmac\Exception\MalformedResponseException;
use Drupal\acq_cart\Cart;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Http\ClientFactory as HttpClientFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Serialization\Json;

/**
 * CheckoutComAPIWrapper class.
 */
class CheckoutComAPIWrapper {

  use StringTranslationTrait;

  // Authorize payment endpoint.
  const AUTHORIZE_PAYMENT_ENDPOINT = 'charges/token';

  // Saved card payment endpoint.
  const CARD_PAYMENT_ENDPOINT = 'charges/card';

  // Void payment endpoint.
  const VOID_PAYMENT_ENDPOINT = 'charges/{id}/void';

  // Void payment amount.
  const VOID_PAYMENT_AMOUNT = 1.0;

  // 3D secure charge mode.
  const CHARGE_MODE_3D = '2';

  // 3D secure autocapture.
  const AUTOCAPTURE = 'Y';

  /**
   * API Helper service object.
   *
   * @var \Drupal\acq_commerce\APIHelper
   */
  protected $helper;

  /**
   * ClientFactory object.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClientFactory;

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The api helper object.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $apiHelper;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * CheckoutComAPIWrapper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   *   ClientFactory object.
   * @param \Drupal\acq_checkoutcom\ApiHelper $api_helper
   *   ApiHelper object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    HttpClientFactory $http_client_factory,
    ApiHelper $api_helper,
    LoggerChannelFactory $logger_factory
  ) {
    $this->configFactory = $config_factory;
    $this->httpClientFactory = $http_client_factory;
    $this->apiHelper = $api_helper;
    $this->logger = $logger_factory->get('acq_checkoutcom');
  }

  /**
   * Createclient.
   *
   * Create a Guzzle http client configured to connect to the
   * checkout.com instance.
   *
   * @return \GuzzleHttp\Client
   *   Object of initialized client.
   *
   * @throws \InvalidArgumentException
   */
  protected function createClient() {
    $config = $this->configFactory->get('acq_checkoutcom.settings');
    $clientConfig = [
      'base_uri' => $config->get('base_uri')[$config->get('env')],
      'verify'   => TRUE,
      'headers' => [
        'Content-Type' => 'application/json;charset=UTF-8',
        'Authorization' => $this->apiHelper->getSubscriptionKeys('secret_key'),
      ],
    ];

    return $this->httpClientFactory->fromOptions($clientConfig);
  }

  /**
   * TryCheckoutRequest.
   *
   * Try a simple request with the Guzzle client, catching / logging request
   * exceptions when needed.
   *
   * @param callable $doReq
   *   Request closure, passed client.
   * @param string $action
   *   Action name for logging.
   *
   * @return mixed
   *   API response.
   *
   * @throws \Exception
   */
  protected function tryCheckoutRequest(callable $doReq, $action) {
    $client = $this->createClient();

    $req_param['commerce'] = [
      'currency' => $this->configFactory->get('acq_commerce.currency')->get('currency_code'),
    ];

    // Make Request.
    try {
      /** @var \GuzzleHttp\Psr7\Response $result */
      $result = $doReq($client, $req_param);
    }
    catch (\Exception $e) {
      $msg = new FormattableMarkup(
        '@action: @class during request: (@code) - @message',
        [
          '@action' => $action,
          '@class' => get_class($e),
          '@code' => $e->getCode(),
          '@message' => $e->getMessage(),
        ]
      );

      $this->logger->error($msg);

      if ($e->getCode() == 404 || $e instanceof MalformedResponseException) {
        throw new \Exception(
          $this->t('Could not make request to checkout.com, please contact administator if the error presist.')
        );
      }
      elseif ($e instanceof RequestException) {
        throw new \UnexpectedValueException($msg, $e->getCode(), $e);
      }
      else {
        throw $e;
      }
    }

    return (Json::decode($result->getBody()));
  }

  /**
   * Process 3d secure payment.
   *
   * @param \Drupal\acq_cart\Cart $cart
   *   The cart object.
   * @param string $endpoint
   *   The end point url to make a request.
   * @param array $params
   *   The params that needed.
   * @param string $caller
   *   The caller from where the method is being called.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response to redirect user to fill 3d secure info.
   *
   * @throws \Exception
   */
  protected function make3dSecurePaymentRequest(Cart $cart, string $endpoint, array $params, $caller = '') {
    // Set parameters required for 3d secure payment.
    $params['chargeMode'] = self::CHARGE_MODE_3D;
    $params['autoCapture'] = self::AUTOCAPTURE;
    $params['successUrl'] = Url::fromRoute('acq_checkoutcom.status', [], ['absolute' => TRUE])->toString();
    $params['failUrl'] = Url::fromRoute('acq_checkoutcom.status', [], ['absolute' => TRUE])->toString();

    $doReq = function ($client, $req_param) use ($endpoint, $params) {
      $opt = ['json' => $req_param['commerce'] + $params];
      return ($client->post($endpoint, $opt));
    };

    try {
      $result = $this->tryCheckoutRequest($doReq, $caller);
    }
    catch (\UnexpectedValueException $e) {
      $this->logger->error('Error occurred while processing checkout.com 3d secure payment process for cart id: %cart_id : %message', [
        '%cart_id' => $cart->id(),
        '%message' => $e->getMessage(),
      ]);
      throw new \Exception(
        new FormattableMarkup(
          'Error occurred while processing checkout.com 3d secure payment process for cart id: %cart_id',
          ['%cart_id' => $cart->id()]
        )
      );
    }

    if ($result['responseCode'] !== '10000' && empty($result['redirectUrl'])) {
      $this->logger->warning('checkout.com card charges request did not process.');

      throw new \Exception(
        new FormattableMarkup(
          'Error occurred while processing checkout.com 3d secure payment process for cart id: %cart_id',
          ['%cart_id' => $cart->id()]
        )
      );
    }
    // Redirect user for 3d verification page.
    if (isset($result['redirectUrl'])) {
      return new RedirectResponse($result['redirectUrl']);
    }
  }

  /**
   * Authorize a card for payment.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   * @param string $endpoint
   *   The end point to call.
   * @param array $params
   *   The array of params.
   * @param string $caller
   *   The caller method name.
   *
   * @return array
   *   Return array of reponse or empty array.
   *
   * @throws \Exception
   */
  protected function authorizeCardForPayment(UserInterface $user, string $endpoint, array $params, $caller = '') {
    $doReq = function ($client, $req_param) use ($endpoint, $params) {
      $opt = ['json' => $req_param['commerce'] + $params];
      return ($client->post($endpoint, $opt));
    };

    try {
      $result = $this->tryCheckoutRequest($doReq, $caller);
    }
    catch (\UnexpectedValueException $e) {
      $this->logger->error('Error occurred while processing card authorization for user: %user : %message', [
        '%user' => $user->getEmail(),
        '%message' => $e->getMessage(),
      ]);
      throw new \Exception(
        new FormattableMarkup(
          'Error occurred while processing card authorization for user: %user',
          ['%user' => $user->getEmail()]
        )
      );
    }

    if (array_key_exists('errorCode', $result)) {
      throw new \Exception('Error Code ' . $result['errorCode'] . ': ' . $result['message']);
    }

    // Validate authorisation.
    if (array_key_exists('status', $result) && $result['status'] === 'Declined') {
      throw new \Exception('Void transaction decliened by checkout.com');
    }

    return $result;
  }

  /**
   * Make void transaction.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   * @param string $endpoint
   *   The end point to call.
   * @param array $params
   *   The array of params.
   * @param string $caller
   *   The caller method.
   *
   * @return array
   *   The array of reponse or empty array.
   *
   * @throws \Exception
   */
  protected function makeVoidTransaction(UserInterface $user, string $endpoint, array $params, $caller = '') {
    $doReq = function ($client, $req_param) use ($endpoint, $params) {
      $opt = ['json' => $req_param['commerce'] + $params];
      return ($client->post($endpoint, $opt));
    };

    try {
      $result = $this->tryCheckoutRequest($doReq, $caller);
    }
    catch (\UnexpectedValueException $e) {
      $this->logger->error('Error occurred while processing card authorization for user: %user : %message', [
        '%user' => $user->getEmail(),
        '%message' => $e->getMessage(),
      ]);
      throw new \Exception(
        new FormattableMarkup(
          'Error occurred while processing card authorization for user: %user',
          ['%user' => $user->getEmail()]
        )
      );
    }

    if (array_key_exists('errorCode', $result)) {
      throw new \Exception('Error Code ' . $result['errorCode'] . ': ' . $result['message']);
    }

    return $result;
  }

  /**
   * Process the payment for given cart.
   *
   * @param \Drupal\acq_cart\Cart $cart
   *   The cart object.
   * @param array $params
   *   The array of parameters.
   * @param bool $is_new
   *   TRUE if processing new card, False otherwise.
   *
   * @throws \Exception
   */
  public function processCardPayment(Cart $cart, array $params, $is_new = FALSE) {
    $response = $this->make3dSecurePaymentRequest(
      $cart,
      $is_new ? self::AUTHORIZE_PAYMENT_ENDPOINT : self::CARD_PAYMENT_ENDPOINT,
      $params,
      __METHOD__
    );
    if ($response instanceof RedirectResponse) {
      $response->send();
    }
  }

  /**
   * Authorize new card with void payment to be saved.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   * @param array $request_param
   *   The payment card params.
   *
   * @return array
   *   Return array of card data to be saved.
   *
   * @throws \Exception
   */
  public function authorizeNewCard(UserInterface $user, array $request_param) {
    $params = [
      'cardToken' => $request_param['cardToken'],
      'email' => $request_param['email'],
      'value' => (float) self::VOID_PAYMENT_AMOUNT * 100,
      'autoCapture' => 'N',
      'description' => 'Saving new card',
      'customerName' => $request_param['name'],
    ];

    // Authorize a card for payment.
    $response = $this->authorizeCardForPayment(
      $user,
      self::AUTHORIZE_PAYMENT_ENDPOINT,
      $params,
      __METHOD__
    );

    // Run the void transaction for the gateway.
    $this->makeVoidTransaction(
      $user,
      strtr(self::VOID_PAYMENT_ENDPOINT, ['{id}' => $response['id']]),
      ['trackId' => ''],
      __METHOD__
    );

    // Prepare the card data to save.
    $cardData = array_filter($response['card'], function ($key) {
      return !in_array($key, [
        'billingDetails',
        'bin',
        'fingerprint',
        'cvvCheck',
        'avsCheck',
      ]);
    }, ARRAY_FILTER_USE_KEY);

    return $cardData;
  }

}
