<?php

namespace Drupal\acq_checkoutcom;

use Acquia\Hmac\Exception\MalformedResponseException;
use Drupal\acq_cart\Cart;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\Connector\ConnectorException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Http\ClientFactory as DrupalClientFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Serialization\Json;

/**
 * CheckoutComAPIWrapper class.
 */
class CheckoutComAPIWrapper {

  use StringTranslationTrait;

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
  protected $drupalClientFactory;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * ACM API Version.
   *
   * @var string
   */
  protected $apiVersion;

  /**
   * APIWrapper service object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * CheckoutComAPIWrapper constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   APIWrapper service object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\Core\Http\ClientFactory $drupal_client_factory
   *   ClientFactory object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(
    APIWrapper $api_wrapper,
    ConfigFactoryInterface $config_factory,
    DrupalClientFactory $drupal_client_factory,
    LoggerChannelFactory $logger_factory
  ) {
    $this->apiVersion = $config_factory->get('acq_commerce.conductor')->get('api_version');
    $this->logger = $logger_factory->get('acq_checkoutcom');
    $this->drupalClientFactory = $drupal_client_factory;
    $this->apiWrapper = $api_wrapper;
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
    $clientConfig = [
      'base_uri' => 'https://sandbox.checkout.com/',
      'verify'   => TRUE,
      'headers' => [
        'Content-Type' => 'application/json;charset=UTF-8',
        'Authorization' => 'sk_test_863d1545-5253-4387-b86b-df6a86797baa',
      ],
    ];

    return $this->drupalClientFactory->fromOptions($clientConfig);
  }

  /**
   * TryCheckoutRequest.
   *
   * Try a simple request with the Guzzle client, catching / logging request
   * e  xceptions if needed.
   *
   * @param callable $doReq
   *   Request closure, passed client.
   * @param string $action
   *   Action name for logging.
   * @param string $reskey
   *   Result data key (or NULL)
   *
   * @return mixed
   *   API response.
   *
   * @throws \Exception
   */
  protected function tryCheckoutRequest(callable $doReq, $action, $reskey = NULL) {
    $client = $this->createClient();

    $req_param['3d'] = [
      'currency' => 'KWD',
      'chargeMode' => 2,
      'autoCapture' => 'Y',
      'successUrl' => Url::fromRoute('acq_checkoutcom.status', [], ['absolute' => TRUE])->toString(),
      'failUrl' => Url::fromRoute('acq_checkoutcom.status', [], ['absolute' => TRUE])->toString(),
    ];

    // Make Request.
    try {
      /** @var \GuzzleHttp\Psr7\Response $result */
      $result = $doReq($client, $req_param);
    }
    catch (\Exception $e) {
      $mesg = $this->t('@action: @class during request: (@code) - @message', [
        '@action' => $action,
        '@class' => get_class($e),
        '@code' => $e->getCode(),
        '@message' => $e->getMessage(),
      ]);

      $this->logger->error($mesg);

      // REDUNDANT at 20180531 because now we set http_errors = false.
      if ($e->getCode() == 404 || $e instanceof MalformedResponseException) {
        throw new \Exception(
          $this->t('Could not make request to checkout.com, please contact administator if the error presist.')
        );
      }
      elseif ($e instanceof RequestException) {
        throw new \UnexpectedValueException($mesg, $e->getCode(), $e);
      }
      else {
        throw $e;
      }
    }

    // This code means we must always return valid JSON for every HTTP status.
    // Is that what we want to enforce? Probably yes.
    $response = Json::decode($result->getBody());

    if (strlen($reskey)) {
      if (!isset($response[$reskey])) {
        throw new \Exception('request successful but did not contain requested data.');
      }
      return ($response[$reskey]);
    }

    return ($response);
  }

  /**
   * Gets the token from Magento.
   *
   * @return mixed
   *   API response containing all the data to be passed on to Cybersource.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getSubscriptionRequest() {
    $endpoint = $this->apiVersion . '/agent/token/checkoutcom';

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    try {
      return $this->apiWrapper->tryAgentRequest($doReq, 'getSubscriptionRequest');
    }
    catch (ConnectorException $e) {
      $this->logger->warning('Error occurred while getting cybersource token for cart id: %cart_id and card type: %card_type: %message', [
        '%message' => $e->getMessage(),
      ]);

      throw new \Exception($e->getMessage(), $e->getCode());
    }
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
    $doReq = function ($client, $req_param) use ($endpoint, $params) {
      $opt = ['json' => $req_param['3d'] + $params];
      return ($client->post($endpoint, $opt));
    };

    try {
      $result = $this->tryCheckoutRequest($doReq, $caller);
    }
    catch (\UnexpectedValueException $e) {
      $this->logger->warning('Error occurred while processing checkout.com 3d secure payment process for cart id: %cart_id : %message', [
        '%cart_id' => $cart->id(),
        '%message' => $e->getMessage(),
      ]);
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    if ($result['responseCode'] !== '10000' && empty($result['redirectUrl'])) {
      $this->logger->warning('checkout.com card charges request did not process.');
    }

    if (isset($result['redirectUrl'])) {
      return new RedirectResponse($result['redirectUrl']);
    }
  }

  /**
   * Process the payment for given cart.
   *
   * @param \Drupal\acq_cart\Cart $cart
   *   The cart object.
   * @param array $params
   *   The array of parameters.
   *
   * @throws \Exception
   */
  public function processNewCardPayment(Cart $cart, array $params) {
    $response = $this->make3dSecurePaymentRequest($cart, '/api2/v2/charges/token', $params, 'process3dSecurePayment');
    if ($response instanceof RedirectResponse) {
      $response->send();
    }
  }

  /**
   * Process the payment for given cart.
   *
   * @param \Drupal\acq_cart\Cart $cart
   *   The cart object.
   * @param array $params
   *   The array of parameters.
   *
   * @throws \Exception
   */
  public function processStoredCardPayment(Cart $cart, array $params) {
    $response = $this->make3dSecurePaymentRequest($cart, '/api2/v2/charges/card', $params, 'processStoredCardPayment');
    if ($response instanceof RedirectResponse) {
      $response->send();
    }
  }

}
