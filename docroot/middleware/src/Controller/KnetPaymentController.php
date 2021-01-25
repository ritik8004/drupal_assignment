<?php

namespace App\Controller;

use App\Helper\CookieHelper;
use App\Service\Cart;
use App\Service\Knet\KnetHelper;
use App\Service\PaymentData;
use App\Service\SessionStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Service\Orders;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Service\Config\SystemSettings;
use App\Service\CartErrorCodes;

/**
 * Contains callback methods for Knet Payment.
 */
class KnetPaymentController extends PaymentController {

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Service for cart interaction.
   *
   * @var \App\Service\Cart
   */
  protected $cart;

  /**
   * K-Net Helper.
   *
   * @var \App\Service\Knet\KnetHelper
   */
  protected $knetHelper;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Payment Data provider.
   *
   * @var \App\Service\PaymentData
   */
  protected $paymentData;

  /**
   * Session Storage service.
   *
   * @var \App\Service\SessionStorage
   */
  protected $session;

  /**
   * K-Net response data.
   *
   * @var array
   */
  protected $knetResponseData = [];

  /**
   * KnetPaymentController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\Cart $cart
   *   Service for cart interaction.
   * @param \App\Service\Knet\KnetHelper $knet_helper
   *   K-Net Helper.
   * @param \App\Service\PaymentData $payment_data
   *   Payment Data provider.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\SessionStorage $session
   *   Session Storage service.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   * @param \App\Service\Orders $order
   *   Order service.
   */
  public function __construct(
    RequestStack $request,
    Cart $cart,
    KnetHelper $knet_helper,
    PaymentData $payment_data,
    LoggerInterface $logger,
    SessionStorage $session,
    SystemSettings $settings,
    Orders $order
  ) {
    parent::__construct($logger, $settings, $cart, $order);
    $this->request = $request->getCurrentRequest();
    $this->cart = $cart;
    $this->knetHelper = $knet_helper;
    $this->paymentData = $payment_data;
    $this->logger = $logger;
    $this->session = $session;
  }

  /**
   * Handle K-Net response callback.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to success page.
   *
   * @throws \Doctrine\DBAL\DBALException
   * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function handleKnetResponse() {
    $data = $this->request->request->all();

    if (empty($data)) {
      $this->logger->error('No data in POST found in knet response page.');
      throw new AccessDeniedHttpException();
    }

    try {
      $data = $this->knetHelper->parseAndPrepareKnetData($data);
    }
    catch (\Exception $e) {
      $this->logger->error('K-Net is not configured properly. POST: @message', [
        '@message' => json_encode($data),
      ]);

      throw new AccessDeniedHttpException();
    }

    $quote_id = $data['udf3'] ?? NULL;
    if (empty($quote_id)) {
      $this->logger->error('Invalid KNET response call found.<br>POST: @message', [
        '@message' => json_encode($data),
      ]);
      throw new AccessDeniedHttpException();
    }

    $response['payment_id'] = $data['paymentid'];
    $response['result'] = $data['result'];
    $response['post_date'] = $data['postdate'];
    $response['transaction_id'] = $data['tranid'] ?? '';
    $response['auth_code'] = $data['auth'];
    $response['ref'] = $data['ref'];
    $response['tracking_id'] = $data['trackid'];
    $response['customer_id'] = (int) $data['udf2'];
    $response['quote_id'] = (int) $data['udf3'];
    $response['state_key'] = $data['udf4'];

    // Assign to property so that can be used other places.
    $this->knetResponseData = $response;

    try {
      $state = $this->validateKnetRequest('response', $response['state_key']);
    }
    catch (\Exception $e) {
      if ($e->getCode() === 302) {
        return new RedirectResponse($e->getMessage(), 302);
      }

      throw $e;
    }

    // Set the langcode for processing as request from K-Net does not
    // contains language info.
    static::$externalPaymentLangcode = $state['data']['langcode'];

    if ($response['result'] !== 'CAPTURED') {
      $this->logger->error('KNET result is not captured, transaction failed.<br>POST: @message<br>Cart: @cart<br>State: @state', [
        '@message' => json_encode($data),
        '@state' => json_encode($state),
      ]);

      return $this->handleKnetError($response['state_key']);
    }

    if ($state['data']['cart_id'] != $response['quote_id'] || $state['data']['order_id'] != $response['tracking_id']) {
      $this->logger->error('KNET response data dont match data in state variable.<br>POST: @message<br>Cart: @cart<br>State: @state', [
        '@message' => json_encode($data),
        '@state' => json_encode($state),
      ]);

      return $this->getKnetErrorResponse($state, 'KNET response data dont match data in state variable.');
    }

    $cart = $this->cart->getCart();
    if ($data['amt'] != $cart['totals']['grand_total']) {
      $this->logger->error('Amount currently in cart dont match amount in state variable.<br>POST: @message<br>Cart: @cart<br>State: @state', [
        '@message' => json_encode($data),
        '@state' => json_encode($state),
        '@cart' => $this->cart->getCartDataToLog($cart),
      ]);

      return $this->getKnetErrorResponse($state, 'KNET response data dont match data in state variable.');
    }

    $this->logger->info('KNET payment complete for @quote_id.<br>@message', [
      '@quote_id' => $response['quote_id'],
      '@message' => json_encode($data),
    ]);

    // Delete the payment data from our custom table now.
    $this->paymentData->deletePaymentDataByCartId((int) $response['quote_id']);

    $redirect = new RedirectResponse('/' . $state['data']['langcode'] . '/checkout', 302);

    try {
      $payment_data = [
        'method' => 'knet',
        'additional_data' => $response,
      ];

      // Push the additional data to cart.
      $payment_updated = $this->cart->updatePayment($payment_data, ['attempted_payment' => 1]);
      if (empty($payment_updated) || !empty($payment_updated['error'])) {
        throw new \Exception($payment_updated['error_message'], $payment_updated['error_code']);
      }

      // Place order.
      $order = $this->cart->placeOrder(['paymentMethod' => $payment_data]);
      if (empty($order) || !empty($order['error'])) {
        throw new \Exception($order['error_message'] ?? 'Place order failed', $order['error_code'] ?? 500);
      }

      $redirect->setTargetUrl('/' . $state['data']['langcode'] . '/checkout/confirmation?id=' . $order['secure_order_id']);
      $redirect->headers->setCookie(CookieHelper::create('middleware_order_placed', 1, strtotime('+1 year')));

      // Add success message in logs.
      $this->logger->info('Placed order. Cart: @cart. Payment method @method.', [
        '@cart' => $this->cart->getCartDataToLog($cart),
        '@method' => 'knet',
      ]);
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to place order for cart @cart_id with message: @message', [
        '@cart_id' => $cart['cart']['id'],
        '@message' => $e->getMessage(),
      ]);

      $this->cart->cancelCartReservation($e->getMessage());

      $payment_data = [
        'status' => $this->getPaymentFailedStatus($e->getCode()),
        'payment_method' => 'knet',
        'data' => [
          'transaction_id' => !empty($response['transaction_id']) ? $response['transaction_id'] : $response['quote_id'],
          'payment_id' => $response['payment_id'],
          'result_code' => $response['result'],
          'order_id' => $cart['cart']['extension_attributes']['real_reserved_order_id'] ?? '',
        ],
      ];

      $redirectUrl = '/checkout';

      if ($e->getCode() === CartErrorCodes::CART_CHECKOUT_QUANTITY_MISMATCH) {
        $payment_data['code'] = $e->getCode();
        $payment_data['message'] = $e->getMessage();
        $redirectUrl = '/cart';
      }

      $redirect->headers->setCookie(CookieHelper::create('middleware_payment_error', json_encode($payment_data), strtotime('+1 year')));
      $redirect->setTargetUrl('/' . $state['data']['langcode'] . $redirectUrl);
    }

    return $redirect;
  }

  /**
   * Handle K-Net error callback.
   *
   * @param string $state_key
   *   State key.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to checkout.
   *
   * @throws \Exception
   */
  public function handleKnetError(string $state_key) {
    try {
      $data = $this->validateKnetRequest('error', $state_key);
    }
    catch (\Exception $e) {
      $this->cart->cancelCartReservation($e->getMessage());

      if ($e->getCode() === 302) {
        return new RedirectResponse($e->getMessage(), 302);
      }

      throw $e;
    }

    $message = 'User either cancelled or response url returned error.';

    $this->logger->error('KNET payment failed for @quote_id: @message', [
      '@quote_id' => $data['data']['cart_id'],
      '@message' => $message,
    ]);

    return $this->getKnetErrorResponse($data, $message);
  }

  /**
   * Get the KNET error response.
   *
   * Also attempt cancel reservation with debug info.
   *
   * @param array $data
   *   State / payment data.
   * @param string $message
   *   Message for cancellation.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Response object.
   */
  protected function getKnetErrorResponse(array $data, string $message) {
    $message .= PHP_EOL . 'Debug info:' . PHP_EOL;
    foreach ($data as $key => $value) {
      $value = is_array($value) ? json_encode($value) : $value;
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->cart->cancelCartReservation($message);

    $response = new RedirectResponse('/' . $data['data']['langcode'] . '/checkout', 302);

    $payment_data = [
      'status' => self::PAYMENT_FAILED_VALUE,
      'payment_method' => 'knet',
    ];

    if (!empty($this->knetResponseData)) {
      $payment_data['data'] = [
        'transaction_id' => $this->knetResponseData['transaction_id'] ?? $this->knetResponseData['quote_id'],
        'payment_id' => $this->knetResponseData['payment_id'] ?? '',
        'result_code' => $this->knetResponseData['result'] ?? '',
      ];
    }

    $response->headers->setCookie(CookieHelper::create('middleware_payment_error', json_encode($payment_data), strtotime('+1 year')));
    return $response;
  }

  /**
   * Basic validations for checkout.com callbacks.
   *
   * @param string $callback
   *   Callback type response/error.
   * @param string $state_key
   *   State key / unique key.
   *
   * @return array
   *   Payment data if available.
   *
   * @throws \Doctrine\DBAL\DBALException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function validateKnetRequest(string $callback, string $state_key) {
    if (empty($state_key)) {
      $this->logger->warning('K-Net @callback callback requested with empty token.', [
        '@callback' => $callback,
      ]);

      throw new NotFoundHttpException('Payment token missing.');
    }

    $data = $this->paymentData->getPaymentDataByUniqueId($state_key);
    if (empty($data)) {
      $this->logger->warning('KNET @callback page requested with invalid state_key: @state_key', [
        '@state_key' => $state_key,
        '@callback' => $callback,
      ]);

      throw new \Exception('/' . $data['data']['langcode'] . '/checkout', 302);
    }

    $cart_id = $this->cart->getCartId();
    if (empty($cart_id)) {
      // We get cases where cookies are not forwarded on redirects in mobile.
      $this->session->updateDataInSession(Cart::SESSION_STORAGE_KEY, (int) $data['data']['cart_id']);
    }
    elseif ($data['data']['cart_id'] != $cart_id) {
      $this->logger->error('KNET @callback callback requested with cart not matching in session. Data: @message, Cart ID in session @cart_id', [
        '@message' => json_encode($data),
        '@cart_id' => $cart_id,
        '@callback' => $callback,
      ]);

      throw new \Exception('/' . $data['data']['langcode'] . '/checkout', 302);
    }

    $cart = $this->cart->getCart(TRUE);
    if (empty($cart) || !empty($cart['error'])) {
      $this->logger->error('KNET @callback callback requested but not able to load cart for the payment data. Data: @message', [
        '@message' => json_encode($data),
        '@callback' => $callback,
      ]);

      throw new \Exception('/' . $data['data']['langcode'] . '/checkout', 302);
    }

    return $data;
  }

}
