<?php

namespace Drupal\acq_checkoutcom;

use Acquia\Hmac\Exception\MalformedResponseException;
use Drupal\acq_cart\Cart;
use Drupal\acq_cart\CartStorageInterface;
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
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * CheckoutComAPIWrapper class.
 */
class CheckoutComAPIWrapper {

  use StringTranslationTrait;

  // Key that contains redirect url.
  const REDIRECT_URL = 'redirectUrl';

  // Authorize payment endpoint.
  const AUTHORIZE_PAYMENT_ENDPOINT = 'charges/token';

  // Saved card payment endpoint.
  const CARD_PAYMENT_ENDPOINT = 'charges/card';

  // Void payment endpoint.
  const VOID_PAYMENT_ENDPOINT = 'charges/{id}/void';

  // Void payment amount.
  const VOID_PAYMENT_AMOUNT = 1.0;

  // 3D secure charge mode.
  const VERIFY_3DSECURE = '2';

  // 3D secure autocapture.
  const AUTOCAPTURE = 'Y';

  // API response success code.
  const SUCCESS_CODE = '10000';

  // Mada bins file name.
  const KEY_MADA_BINS_FILE = 'mada_bins.csv';

  // Mada bins test file name.
  const KEY_MADA_BINS_FILE_TEST = 'mada_bins_test.csv';

  // Multiply currency value to hundreds.
  const MULTIPLY_HUNDREDS = 100;

  // The option that determines whether the payment method associated with
  // the successful transaction should be stored in the Vault.
  const STORE_IN_VAULT_ON_SUCCESS = 'storeInVaultOnSuccess';

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
   * The cart storage.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

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
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart storage.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    HttpClientFactory $http_client_factory,
    ApiHelper $api_helper,
    CartStorageInterface $cart_storage,
    RequestStack $request,
    LoggerChannelFactory $logger_factory
  ) {
    $this->configFactory = $config_factory;
    $this->httpClientFactory = $http_client_factory;
    $this->apiHelper = $api_helper;
    $this->cartStorage = $cart_storage;
    $this->request = $request->getCurrentRequest();
    $this->logger = $logger_factory->get('acq_checkoutcom');
  }

  /**
   * Get cart object.
   *
   * @return \Drupal\acq_cart\CartInterface
   *   return cart object or redirect to cart page.
   */
  public function getCart() {
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      $response = new RedirectResponse(Url::fromRoute('acq_cart.cart')->toString());
      $response->send();
      exit;
    }

    return $cart;
  }

  /**
   * Is mada bin check enabled.
   *
   * @return bool
   *   Return TRUE for enabled, FALSE otherwise.
   */
  public function isMadaEnabled() {
    return $this->apiHelper->getCheckoutcomConfig('mada_enabled');
  }

  /**
   * Return the MADA BINS file path.
   *
   * @return string
   *   Return the mada bin file path.
   */
  public function getMadaBinsPath() {
    return (string) '/files/' . (($this->isLive())
      ? self::KEY_MADA_BINS_FILE
      : self::KEY_MADA_BINS_FILE_TEST);
  }

  /**
   * Checks if the given bin is belong to mada bin.
   *
   * @return bool
   *   Return true if one of the mada bin, false otherwise.
   */
  public function isMadaBin($bin) {
    // Set mada bin file path.
    $mada_bin_csv_path = drupal_get_path('module', 'acq_checkoutcom') . $this->getMadaBinsPath();

    // Read CSV rows.
    $mada_bin_csv_file = fopen($mada_bin_csv_path, 'r+');

    $mada_bin_csv_data = [];
    while ($mada_bin_csv_row = fgetcsv($mada_bin_csv_file)) {
      $mada_bin_csv_data[] = $mada_bin_csv_row;
    }
    fclose($mada_bin_csv_file);

    // Remove the first row of csv columns.
    unset($mada_bin_csv_data[0]);

    // Build the mada bin array.
    $mada_bin_array = array_map(function ($row) {
      return $row[1];
    }, $mada_bin_csv_data);

    return in_array($bin, $mada_bin_array);
  }

  /**
   * Get current environment is live or not.
   *
   * @return bool
   *   Return true if current env is live else false.
   */
  protected function isLive() {
    return (
      $this->apiHelper->getCheckoutcomConfig('environment') == 'live'
    );
  }

  /**
   * Get the base uri for api call.
   *
   * @return string
   *   Return base uri for sandbox or live.
   */
  protected function getBaseUri(): string {
    $env = $this->apiHelper->getCheckoutcomConfig('environment');
    return $this->configFactory
      ->get('acq_checkoutcom.settings')
      ->get('base_uri')[$env];
  }

  /**
   * Crate a new client object.
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
      'base_uri' => $this->getBaseUri(),
      'verify'   => TRUE,
      'headers' => [
        'Content-Type' => 'application/json;charset=UTF-8',
        'Authorization' => $this->apiHelper->getCheckoutcomConfig('secret_key'),
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

    // Make Request.
    try {
      /** @var \GuzzleHttp\Psr7\Response $result */
      $result = $doReq($client, [
        'currency' => $this->configFactory->get('acq_commerce.currency')->get('iso_currency_code'),
      ]);
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
          $this->t('Could not make request to checkout.com, please contact administator if the error persist.')
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
    $doReq = function ($client, $req_param) use ($endpoint, $params) {
      $opt = ['json' => $req_param + $params];
      return ($client->post($endpoint, $opt));
    };

    try {
      $response = $this->tryCheckoutRequest($doReq, $caller);
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

    if (isset($response['responseCode']) && !empty($response[self::REDIRECT_URL]) && (int) $response['responseCode'] == self::SUCCESS_CODE) {
      return new RedirectResponse($response[self::REDIRECT_URL]);
    }
    else {
      $this->logger->warning('checkout.com card charges request did not process.');

      throw new \Exception(
        new FormattableMarkup(
          'Error occurred while processing checkout.com 3d secure payment process for cart id: %cart_id',
          ['%cart_id' => $cart->id()]
        )
      );
    }
  }

  /**
   * Prepare cart items array to send with payment request.
   *
   * @return array
   *   Array of cart items list.
   */
  protected function getCartItems() {
    $items = $this->getCart()->items();

    $products = [];
    foreach ($items as $line_item) {
      // Ensure object notation.
      $line_item = (object) $line_item;
      $products[] = [
        'sku' => $line_item->sku,
        'name' => $line_item->name['#title'],
        'quantity' => $line_item->qty,
        'price' => $line_item->price,
        'description' => NULL,
      ];
    }
    return $products;
  }

  /**
   * Get billing or shipping address info.
   *
   * @param string $type
   *   The address type, billing or shipping.
   *
   * @return array
   *   The keyed array of address info as required by checkout.com
   */
  protected function getAddressDetails($type = 'billing') {
    if (!in_array($type, ['billing', 'shipping'])) {
      return [];
    }

    $cart = $this->getCart();
    $address = ($type == 'shipping')
      ? $cart->getShipping()
      : $cart->getBilling();

    return [
      'addressLine1' => $address['street'],
      'addressLine2' => $address['street2'],
      'postcode' => NULL,
      'country' => $address['country_id'],
      'state' => NULL,
      'city' => $address['city'],
    ];
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
      $opt = ['json' => $req_param + $params];
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
      $opt = ['json' => $req_param + $params];
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
   * Process the 3d secure payment for given cart.
   *
   * @param \Drupal\acq_cart\Cart $cart
   *   The cart object.
   * @param array $params
   *   The array of parameters.
   *
   * @throws \Exception
   */
  public function processCardPayment(Cart $cart, array $params) {
    // Set parameters required for 3d secure payment.
    $params['chargeMode'] = self::VERIFY_3DSECURE;
    // Capture payment immediately, values 0 to 168 (0 to 7 days).
    $params['autoCapTime'] = '0';
    $params['autoCapture'] = self::AUTOCAPTURE;
    $params['attemptN3D'] = FALSE;
    // Use the IP address from Acquia Cloud ENV variable.
    $params['customerIp'] = $_ENV['AH_CLIENT_IP'] ?? '';
    $params['successUrl'] = Url::fromRoute('acq_checkoutcom.payment_success', [], ['absolute' => TRUE])->toString();
    $params['failUrl'] = Url::fromRoute('acq_checkoutcom.payment_fail', [], ['absolute' => TRUE])->toString();
    $params['trackId'] = $this->getCart()->getExtension('real_reserved_order_id');
    $params['products'] = $this->getCartItems();
    $params['billingDetails'] = $this->getAddressDetails('billing');
    $params['shippingDetails'] = $this->getAddressDetails('shipping');

    $response = $this->make3dSecurePaymentRequest(
      $cart,
      !empty($params['cardToken']) ? self::AUTHORIZE_PAYMENT_ENDPOINT : self::CARD_PAYMENT_ENDPOINT,
      $params,
      __METHOD__
    );
    if ($response instanceof RedirectResponse) {
      $response->send();
      exit;
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
