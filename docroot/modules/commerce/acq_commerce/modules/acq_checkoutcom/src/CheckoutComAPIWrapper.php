<?php

namespace Drupal\acq_checkoutcom;

use Acquia\Hmac\Exception\MalformedResponseException;
use Drupal\acq_cart\Cart;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Http\ClientFactory as HttpClientFactory;
use Drupal\Core\Messenger\MessengerInterface;
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
  const ENDPOINT_AUTHORIZE_PAYMENT = 'charges/token';

  // Saved card payment endpoint.
  const ENDPOINT_CARD_PAYMENT = 'charges/card';

  // Void payment endpoint.
  const ENDPOINT_VOID_PAYMENT = 'charges/{id}/void';

  // Void payment endpoint.
  const ENDPOINT_CHARGES_INFO = 'charges/{payment_token}';

  // Void payment amount.
  const VOID_PAYMENT_AMOUNT = 1.0;

  // 3D secure charge mode.
  const VERIFY_3DSECURE = '2';

  // Auto capture yes.
  const AUTOCAPTURE_YES = 'Y';

  // Auto capture no.
  const AUTOCAPTURE_NO = 'N';

  // API response success code.
  const SUCCESS = '10000';

  // Mada bins file name.
  const MADA_BINS_FILE = 'mada_bins.csv';

  // Mada bins test file name.
  const MADA_BINS_FILE_TEST = 'mada_bins_test.csv';

  // The option that determines whether the payment method associated with
  // the successful transaction should be stored in the Vault.
  const STORE_IN_VAULT_ON_SUCCESS = 'storeInVaultOnSuccess';

  /**
   * Currencies where charge amount is full.
   *
   * @var array
   * @ref https://github.com/checkout/checkout-magento2-plugin/blob/1.0.44/Model/Adapter/ChargeAmountAdapter.php#L32
   */
  const FULL_VALUE_CURRENCIES = [
    'BYR', 'BIF', 'DJF', 'GNF', 'KMF',
    'XAF', 'CLF', 'XPF', 'JPY', 'PYG',
    'RWF', 'KRW', 'VUV', 'VND', 'XOF',
  ];

  /**
   * Currencies where charge amount is divided by 1000.
   *
   * @var array
   * @ref https://github.com/checkout/checkout-magento2-plugin/blob/1.0.44/Model/Adapter/ChargeAmountAdapter.php#L39
   */
  const DIV_1000_VALUE_CURRENCIES = ['BHD', 'KWD', 'OMR', 'JOD'];

  // @ref https://github.com/checkout/checkout-magento2-plugin/blob/1.0.44/Model/Adapter/ChargeAmountAdapter.php#L41
  const DIV_1000 = 1000;

  // @ref https://github.com/checkout/checkout-magento2-plugin/blob/1.0.44/Model/Adapter/ChargeAmountAdapter.php#L43
  const DIV_100 = 100;

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
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

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
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    HttpClientFactory $http_client_factory,
    ApiHelper $api_helper,
    CartStorageInterface $cart_storage,
    RequestStack $request,
    MessengerInterface $messenger,
    LoggerChannelFactory $logger_factory
  ) {
    $this->configFactory = $config_factory;
    $this->httpClientFactory = $http_client_factory;
    $this->apiHelper = $api_helper;
    $this->cartStorage = $cart_storage;
    $this->request = $request->getCurrentRequest();
    $this->messenger = $messenger;
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
      ? self::MADA_BINS_FILE
      : self::MADA_BINS_FILE_TEST);
  }

  /**
   * Checks if the given bin is belong to mada bin.
   *
   * @param string $bin
   *   The card bin to verify.
   *
   * @return bool
   *   Return true if one of the mada bin, false otherwise.
   */
  public function isMadaBin($bin) {
    // Set mada bin file path.
    $mada_bin_csv_path = drupal_get_path('module', 'acq_checkoutcom') . $this->getMadaBinsPath();

    // Read CSV rows.
    $mada_bin_csv_file = fopen($mada_bin_csv_path, 'r');

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
   * Returns transformed amount by the given currency code.
   *
   * @param float $amount
   *   The amount of order to convert.
   *
   * @return float
   *   The processed amount.
   */
  public function getCheckoutAmount($amount) {
    $currencyCode = $this->configFactory->get('acq_commerce.currency')->get('iso_currency_code');
    if (in_array($currencyCode, self::FULL_VALUE_CURRENCIES, TRUE)) {
      return (float) $amount;
    }

    if (in_array($currencyCode, self::DIV_1000_VALUE_CURRENCIES, TRUE)) {
      return (float) ($amount * self::DIV_1000);
    }

    return (float) ($amount * self::DIV_100);
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
        throw new \Exception($msg);
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
  protected function request3dSecurePayment(Cart $cart, string $endpoint, array $params, $caller = '') {
    $doReq = function ($client, $req_param) use ($endpoint, $params) {
      $opt = ['json' => $req_param + $params];
      return ($client->post($endpoint, $opt));
    };

    try {
      $response = $this->tryCheckoutRequest($doReq, $caller);
    }
    catch (\UnexpectedValueException $e) {
      $this->logger->error(
        'Error occurred while trying to get redirect url for checkout.com for cart id: %cart_id with param: @param :: %message',
        [
          '%cart_id' => $cart->id(),
          '%message' => $e->getMessage(),
          '@params' => Json::encode($params),
        ]
      );

      // Show generic error message to user and redirect to payment page.
      $this->setGenericErrorMessage();
      $this->redirectToPayment();
    }

    if (isset($response['responseCode']) && !empty($response[self::REDIRECT_URL])) {
      $redirect = new RedirectResponse($response[self::REDIRECT_URL]);
      $redirect->send();
      exit;
    }
    else {
      $this->logger->warning(
        'checkout.com card charges request did not process, getting response: @response.',
        ['@response' => Json::encode($response)]
      );

      // Show generic error message to user and redirect to payment page.
      $this->setGenericErrorMessage();
      $this->redirectToPayment();
    }
  }

  /**
   * Display generic message of payment fail.
   */
  public function setGenericErrorMessage() {
    // Show generic message to user.
    $this->messenger->addError(
      $this->t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.')
    );
  }

  /**
   * Redirect to payment page.
   */
  public function redirectToPayment() {
    $response = new RedirectResponse(Url::fromRoute('acq_checkout.form', ['step' => 'payment'])->toString());
    $response->send();
    exit;
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
   *   The array of response or empty array.
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
    $params['autoCapture'] = (isset($params['udf1']) && $params['udf1'] == 'MADA') ? self::AUTOCAPTURE_YES : self::AUTOCAPTURE_NO;
    $params['attemptN3D'] = FALSE;
    // Use the IP address from Acquia Cloud ENV variable.
    $params['customerIp'] = $_ENV['AH_CLIENT_IP'] ?? '';
    $params['successUrl'] = Url::fromRoute('acq_checkoutcom.payment_success', [], ['absolute' => TRUE])->toString();
    $params['failUrl'] = Url::fromRoute('acq_checkoutcom.payment_fail', [], ['absolute' => TRUE])->toString();
    $params['trackId'] = $this->getCart()->getExtension('real_reserved_order_id');
    $params['products'] = $this->getCartItems();
    $params['billingDetails'] = $this->getAddressDetails('billing');
    $params['shippingDetails'] = $this->getAddressDetails('shipping');

    $response = $this->request3dSecurePayment(
      $cart,
      !empty($params['cardToken']) ? self::ENDPOINT_AUTHORIZE_PAYMENT : self::ENDPOINT_CARD_PAYMENT,
      $params,
      __METHOD__
    );
    if ($response instanceof RedirectResponse) {
      $response->send();
      exit;
    }
  }

  /**
   * Get charges info based on payment token.
   *
   * @param string $payment_token
   *   The payment token.
   *
   * @return mixed
   *   Return payment details.
   *
   * @throws \Exception
   */
  public function getChargesInfo($payment_token) {
    $endpoint = strtr(self::ENDPOINT_CHARGES_INFO, ['{payment_token}' => $payment_token]);
    $doReq = function ($client, $req_param) use ($endpoint) {
      return ($client->get($endpoint, []));
    };

    $cart = $this->cartStorage->getCart(FALSE);
    try {
      $result = $this->tryCheckoutRequest($doReq, __METHOD__);
    }
    catch (\UnexpectedValueException $e) {
      $this->logger->error(
        'Error occurred while getting info on payment failure, for cart: @cart_id, payment token: @payment_token with message: @message.',
        [
          '@cart_id' => $cart->id(),
          '@mail' => $cart->customerEmail(),
          '@payment_token' => $payment_token,
          '@message' => $e->getMessage(),
        ]
      );
    }

    return $result;
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
      self::ENDPOINT_AUTHORIZE_PAYMENT,
      $params,
      __METHOD__
    );

    // Run the void transaction for the gateway.
    $this->makeVoidTransaction(
      $user,
      strtr(self::ENDPOINT_VOID_PAYMENT, ['{id}' => $response['id']]),
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
