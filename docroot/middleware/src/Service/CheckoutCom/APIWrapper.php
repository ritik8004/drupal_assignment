<?php

namespace App\Service\CheckoutCom;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * APIWrapper class.
 */
class APIWrapper {

  // Magento method, to set for 2d vault (tokenized card) transaction.
  const CHECKOUT_COM_VAULT_METHOD = 'checkout_com_cc_vault';

  // Key that contains redirect url.
  const REDIRECT_URL = 'redirectUrl';

  // Authorize payment endpoint.
  const ENDPOINT_AUTHORIZE_PAYMENT = 'charges/token';

  // Saved card payment endpoint.
  const ENDPOINT_CARD_PAYMENT = 'charges/card';

  // Get charges info from payment token.
  const ENDPOINT_CHARGES_INFO = 'charges/{payment_token}';

  // 3D secure charge mode.
  const VERIFY_3DSECURE = '2';

  // Auto capture yes.
  const AUTOCAPTURE_YES = 'Y';

  // Auto capture no.
  const AUTOCAPTURE_NO = 'N';

  // API response success code.
  const SUCCESS = '10000';

  // The option that determines whether the payment method associated with
  // the successful transaction should be stored in the Vault.
  const STORE_IN_VAULT_ON_SUCCESS = 'storeInVaultOnSuccess';

  // Set udf value for tokenised card.
  const CARD_ID_CHARGE = 'cardIdCharge';

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
   * Checkout.com Helper.
   *
   * @var \App\Service\CheckoutCom\Helper
   */
  protected $helper;

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Mada Validator.
   *
   * @var \App\Service\CheckoutCom\MadaValidator
   */
  protected $madaValidator;

  /**
   * APIWrapper constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\CheckoutCom\Helper $helper
   *   Checkout.com Helper.
   * @param \App\Service\CheckoutCom\MadaValidator $mada_validator
   *   Mada Validator.
   */
  public function __construct(RequestStack $request,
                              Helper $helper,
                              MadaValidator $mada_validator) {
    $this->request = $request->getCurrentRequest();
    $this->helper = $helper;
    $this->madaValidator = $mada_validator;
  }

  /**
   * Prepare cart items array to send with payment request.
   *
   * @param array $items
   *   Cart items.
   *
   * @return array
   *   Array of cart items list.
   */
  protected function getCartItems(array $items) {
    $products = [];
    foreach ($items as $item) {
      $products[] = [
        'sku' => $item['sku'],
        'name' => $item['name'],
        'quantity' => $item['qty'],
        'price' => $item['price'],
        'description' => $item['product_type'],
      ];
    }
    return $products;
  }

  /**
   * Get billing or shipping address info.
   *
   * @param array $cart
   *   Cart data.
   * @param string $type
   *   The address type, billing or shipping.
   *
   * @return array
   *   The keyed array of address info as required by checkout.com
   */
  protected function getAddressDetails(array $cart, string $type) {
    if (!in_array($type, ['billing', 'shipping'])) {
      return [];
    }

    $address = ($type == 'shipping')
      ? $cart['extension_attributes']['shipping_assignments'][0]['shipping']['address']
      : $cart['billing_address'];

    return [
      'addressLine1' => reset($address['street']),
      'addressLine2' => '',
      'postcode' => NULL,
      'country' => $address['country_id'],
      'state' => NULL,
      'city' => $address['city'],
    ];
  }

  /**
   * Is 3D secure check forced.
   *
   * @return bool
   *   Return TRUE for forced, FALSE otherwise.
   */
  public function is3dForced() {
    return $this->helper->getConfig('verify3dsecure');
  }

  /**
   * Get current environment is live or not.
   *
   * @return bool
   *   Return true if current env is live else false.
   */
  protected function isLive() {
    return ($this->helper->getConfig('environment') === 'live');
  }

  /**
   * Get the base uri for api call.
   *
   * @return string
   *   Return base uri for sandbox or live.
   */
  protected function getBaseUri(): string {
    return $this->isLive()
      ? 'https://api2.checkout.com/v2/'
      : 'https://sandbox.checkout.com/api2/v2/';
  }

  /**
   * Log messages if debug settings is enabled.
   *
   * @param string $message
   *   The message to log.
   * @param array $params
   *   The array of parameters to replace.
   */
  protected function logInfo($message, array $params) {
    if ($this->isLive()) {
      return;
    }

    $params = array_map(function ($param) {
      if (is_array($param)) {
        unset($param['card'], $param['shippingDetails'], $param['billingDetails']);
        return json_encode($param);
      }
      else {
        return $param;
      }
    }, $params);

    $params['@cart_id'] = $params['@cart_id'] ?? '';

    // @TODO: Correct it once logging works.
    // $this->logger->info($message, $params);
  }

  /**
   * Returns transformed amount by the given currency code.
   *
   * @param float $amount
   *   The amount of order to convert.
   * @param string $currency
   *   Currency.
   *
   * @return float
   *   The processed amount.
   */
  public function getCheckoutAmount($amount, string $currency) {
    if (in_array($currency, self::FULL_VALUE_CURRENCIES, TRUE)) {
      return (float) $amount;
    }

    if (in_array($currency, self::DIV_1000_VALUE_CURRENCIES, TRUE)) {
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
    $configuration = [
      'base_uri' => $this->getBaseUri(),
      'verify'   => TRUE,
      'headers' => [
        'Content-Type' => 'application/json;charset=UTF-8',
        'Authorization' => $this->helper->getConfig('secret_key'),
      ],
    ];

    return (new Client($configuration));
  }

  /**
   * TryCheckoutRequest.
   *
   * Try a simple request with the Guzzle client, catching / logging request
   * exceptions when needed.
   *
   * @param callable $doReq
   *   Request closure, passed client.
   *
   * @return mixed
   *   API response.
   *
   * @throws \Exception
   */
  protected function tryCheckoutRequest(callable $doReq) {
    $client = $this->createClient();

    // Make Request.
    try {
      /** @var \GuzzleHttp\Psr7\Response $result */
      $result = $doReq($client);

      $response = json_decode($result->getBody(), TRUE);
    }
    catch (\Exception $e) {
      $msg = sprintf('%s during request: (%s) - %s', get_class($e), $e->getCode(), $e->getMessage());

      // $this->logger->error($msg);
      if ($e->getCode() == 404) {
        throw new \Exception($msg);
      }
      elseif ($e instanceof RequestException) {
        throw new \UnexpectedValueException($msg, $e->getCode(), $e);
      }
      else {
        throw $e;
      }
    }

    $this->logInfo('checkout.com: for cart: @cart_id response for @action is: @response', [
      '@response' => $response,
    ]);

    return $response;
  }

  /**
   * Request 3D Secure payment.
   *
   * @param array $cart
   *   Cart data.
   * @param array $payment_data
   *   Payment data.
   * @param string $endpoint
   *   Endpoint.
   *
   * @return array
   *   Response from checkout.com.
   *
   * @throws \Exception
   */
  public function request3dSecurePayment(array $cart, array $payment_data, string $endpoint) {
    $params = [
      'value' => $this->getCheckoutAmount($cart['totals']['grand_total'], $cart['totals']['quote_currency_code']),
      'currency' => $cart['totals']['quote_currency_code'],
      'email' => $cart['cart']['customer']['email'],
    ];

    if (!empty($payment_data['card_token_id'])) {
      // If the current card is "MADA" then set the value else empty.
      $params['udf1'] = $payment_data['udf1'] ?? '';
      // Save card for future - STORE_IN_VAULT_ON_SUCCESS.
      $params['udf3'] = $payment_data['udf3'] ?? '';
      $params['cardToken'] = $payment_data['card_token_id'];
    }
    elseif (!empty($payment_data['cardId'])) {
      // Set cardID, cvv and udf2 = 'cardIdCharge' values.
      $params = array_merge($params, $payment_data);
    }

    // Set parameters required for 3d secure payment.
    $params['chargeMode'] = self::VERIFY_3DSECURE;
    // Capture payment immediately, values 0 to 168 (0 to 7 days).
    $params['autoCapTime'] = '0';
    $params['autoCapture'] = (isset($payment_data['udf1']) && $payment_data['udf1'] == 'MADA')
      ? self::AUTOCAPTURE_YES
      : self::AUTOCAPTURE_NO;

    $params['attemptN3D'] = FALSE;

    // Use the IP address from Acquia Cloud ENV variable.
    $params['customerIp'] = $_ENV['AH_CLIENT_IP'] ?? $this->request->getClientIp();

    // We hard code HTTPS here as varnish request to middleware is always http.
    $host = 'https://' . $this->request->getHttpHost() . '/middleware/public/payment/';
    $params['successUrl'] = $host . 'checkout-com-success';
    $params['failUrl'] = $host . 'checkout-com-error';

    $params['trackId'] = $cart['cart']['extension_attributes']['real_reserved_order_id'];

    $params['products'] = $this->getCartItems($cart['cart']['items']);
    $params['billingDetails'] = $this->getAddressDetails($cart['cart'], 'billing');
    $params['shippingDetails'] = $this->getAddressDetails($cart['cart'], 'shipping');

    $this->logInfo('checkout.com: for cart: @cart_id api 3d request parameters are: @request_param', [
      '@cart_id' => $cart['cart']['id'],
      '@request_param' => $params,
    ]);

    $doReq = function ($client) use ($endpoint, $params) {
      $opt = ['json' => $params];
      return ($client->post($endpoint, $opt));
    };

    return $this->tryCheckoutRequest($doReq);
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
    $doReq = function ($client) use ($endpoint) {
      return ($client->get($endpoint, []));
    };

    try {
      $response = $this->tryCheckoutRequest($doReq, __METHOD__);
    }
    catch (\UnexpectedValueException $e) {
      $this->logger->error(
        'Error occurred while getting payment info for payment token: @payment_token with message: @message.',
        [
          '@payment_token' => $payment_token,
          '@message' => $e->getMessage(),
        ]
      );
    }

    return $response ?? NULL;
  }

  /**
   * Validate MADA bin if enabled.
   *
   * @param string $bin
   *   Card bin.
   *
   * @return bool
   *   TRUE if mada is enabled and card bin verified.
   */
  public function validateMadaBin(string $bin) {
    if (!($this->helper->getConfig('mada_enabled'))) {
      return FALSE;
    }

    return $this->madaValidator->isMadaBin($this->isLive(), $bin);
  }

}
