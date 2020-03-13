<?php

namespace App\Controller;

use App\Service\Cart;
use App\Service\CheckoutCom\APIWrapper;
use App\Service\Knet\KnetHelper;
use App\Service\PaymentData;
use App\Service\SessionStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PaymentController.
 */
class PaymentController {

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
   * Checkout.com API Wrapper.
   *
   * @var \App\Service\CheckoutCom\APIWrapper
   */
  protected $checkoutComApi;

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
   * PaymentController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\Cart $cart
   *   Service for cart interaction.
   * @param \App\Service\CheckoutCom\APIWrapper $checkout_com_api
   *   Checkout.com API Wrapper.
   * @param \App\Service\Knet\KnetHelper $knet_helper
   *   K-Net Helper.
   * @param \App\Service\PaymentData $payment_data
   *   Payment Data provider.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\SessionStorage $session
   *   Session Storage service.
   */
  public function __construct(RequestStack $request,
                              Cart $cart,
                              APIWrapper $checkout_com_api,
                              KnetHelper $knet_helper,
                              PaymentData $payment_data,
                              LoggerInterface $logger,
                              SessionStorage $session) {
    $this->request = $request->getCurrentRequest();
    $this->cart = $cart;
    $this->checkoutComApi = $checkout_com_api;
    $this->knetHelper = $knet_helper;
    $this->paymentData = $payment_data;
    $this->logger = $logger;
    $this->session = $session;
  }

  /**
   * Handle checkout.com payment success callback.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to cart or checkout or confirmation page.
   */
  public function handleCheckoutComSuccess() {
    try {
      $data = $this->validateCheckoutComRequest('success');
    }
    catch (\Exception $e) {
      if ($e->getCode() === 302) {
        return new RedirectResponse($e->getMessage(), 302);
      }

      throw $e;
    }

    $cart = $this->cart->getCart();

    $payment_token = $this->request->query->get('cko-payment-token') ?? '';
    $charges = $this->checkoutComApi->getChargesInfo($payment_token);

    // Validate again.
    if (empty($charges['responseCode']) || $charges['responseCode'] != APIWrapper::SUCCESS) {
      $this->logger->error('3D secure payment came into success but responseCode was not success. Cart id: @id, responseCode: @code, Payment token: @token', [
        '@id' => $cart['cart']['id'],
        '@code' => $data['responseCode'],
        '@token' => $payment_token,
      ]);

      return $this->handleCheckoutComFailure();
    }

    $amount = $this->checkoutComApi->getCheckoutAmount($cart['totals']['grand_total'], $cart['totals']['quote_currency_code']);
    if (empty($charges['value']) || $charges['value'] != $amount) {
      $this->logger->error('3D secure payment came into success with proper responseCode but totals do not match. Cart id: @id, Amount in checkout: @value, Amount in Cart: @total', [
        '@id' => $cart['cart']['id'],
        '@value' => $charges['value'],
        '@total' => $amount,
      ]);

      return $this->handleCheckoutComFailure();
    }

    $response = new RedirectResponse('/' . $data['data']['langcode'] . '/checkout/confirmation', 302);

    try {
      $payment_data = [
        'method' => 'checkout_com',
        'additional_data' => [
          'cko_payment_token' => $payment_token,
        ],
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

      $response->headers->setCookie(Cookie::create('middleware_order_placed', $order['order_id'], strtotime('+1 year'), '/', NULL, TRUE, FALSE));

      // Add success message in logs.
      $this->logger->info('Placed order. Cart: @cart. Payment method @method.', [
        '@cart' => $this->cart->getCartDataToLog($cart),
        '@method' => 'checkout_com',
      ]);
    }
    catch (\Exception $e) {
      $this->logger->error(
        'Failed to place order for cart @cart_id with message: @message',
        ['@cart_id' => $cart['cart']['id'], '@message' => $e->getMessage()]
      );

      $response->headers->setCookie(Cookie::create('middleware_payment_error', 'failed', strtotime('+1 year'), '/', NULL, TRUE, FALSE));
      $response->setTargetUrl('/' . $data['data']['langcode'] . '/checkout');
    }

    return $response;
  }

  /**
   * Handle checkout.com payment error callback.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to cart or checkout page.
   */
  public function handleCheckoutComError() {
    try {
      $data = $this->validateCheckoutComRequest('error');
    }
    catch (\Exception $e) {
      if ($e->getCode() === 302) {
        return new RedirectResponse($e->getMessage(), 302);
      }

      throw $e;
    }

    $response = new RedirectResponse('/' . $data['data']['langcode'] . '/checkout', 302);
    $response->headers->setCookie(Cookie::create('middleware_payment_error', 'declined', strtotime('+1 year'), '/', NULL, TRUE, FALSE));
    return $response;
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

    try {
      $state = $this->validateKnetRequest('response', $response['state_key']);
    }
    catch (\Exception $e) {
      if ($e->getCode() === 302) {
        return new RedirectResponse($e->getMessage(), 302);
      }

      throw $e;
    }

    if ($state['data']['cart_id'] != $response['quote_id'] || $state['data']['order_id'] != $response['tracking_id']) {
      $this->logger->error('KNET response data dont match data in state variable.<br>POST: @message<br>Cart: @cart<br>State: @state', [
        '@message' => json_encode($data),
        '@state' => json_encode($state),
      ]);

      return $this->handleKnetError($response['state_key']);
    }

    $cart = $this->cart->getCart();
    if ($data['amt'] != $cart['totals']['grand_total']) {
      $this->logger->error('Amount currently in cart dont match amount in state variable.<br>POST: @message<br>Cart: @cart<br>State: @state', [
        '@message' => json_encode($data),
        '@state' => json_encode($state),
        '@cart' => $this->cart->getCartDataToLog($cart),
      ]);

      return $this->handleKnetError($response['state_key']);
    }

    if ($response['result'] !== 'CAPTURED') {
      $this->logger->error('KNET result is not captured, transaction failed.<br>POST: @message<br>Cart: @cart<br>State: @state', [
        '@message' => json_encode($data),
        '@state' => json_encode($state),
      ]);

      return $this->handleKnetError($response['state_key']);
    }

    $this->logger->info('KNET payment complete for @quote_id.<br>@message', [
      '@quote_id' => $data['quote_id'],
      '@message' => json_encode($data),
    ]);

    $redirect = new RedirectResponse('/' . $state['data']['langcode'] . '/checkout/confirmation', 302);

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

      $redirect->headers->setCookie(Cookie::create('middleware_order_placed', $order['order_id'], strtotime('+1 year'), '/', NULL, TRUE, FALSE));

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

      $redirect->headers->setCookie(Cookie::create('middleware_payment_error', 'failed', strtotime('+1 year'), '/', NULL, TRUE, FALSE));
      $redirect->setTargetUrl('/' . $state['data']['langcode'] . '/checkout');
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
      if ($e->getCode() === 302) {
        return new RedirectResponse($e->getMessage(), 302);
      }

      throw $e;
    }

    $message = 'User either cancelled or response url returned error.';
    $message .= PHP_EOL . 'Debug info:' . PHP_EOL;
    foreach ($data as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->logger->error('KNET payment failed for @quote_id: @message', [
      '@quote_id' => $data['data']['cart_id'],
      '@message' => $message,
    ]);

    $response = new RedirectResponse('/' . $data['data']['langcode'] . '/checkout', 302);
    $response->headers->setCookie(Cookie::create('middleware_payment_error', 'failed', strtotime('+1 year'), '/', NULL, TRUE, FALSE));
    return $response;
  }

  /**
   * Basic validations for checkout.com callbacks.
   *
   * @param string $callback
   *   Callback type success/error.
   *
   * @return array
   *   Payment data if available.
   *
   * @throws \Doctrine\DBAL\DBALException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function validateCheckoutComRequest(string $callback) {
    $payment_token = $this->request->query->get('cko-payment-token') ?? '';

    if (empty($payment_token)) {
      $this->logger->warning('3D secure @callback callback requested with empty token.', [
        '@callback' => $callback,
      ]);

      throw new NotFoundHttpException('Payment token missing.');
    }

    $data = $this->paymentData->getPaymentDataByUniqueId($payment_token);
    if (empty($data)) {
      $this->logger->error('3D secure payment came into @callback but not able to load payment data. Payment token: @token', [
        '@token' => $payment_token,
        '@callback' => $callback,
      ]);

      throw new NotFoundHttpException();
    }

    $cart_id = $this->cart->getCartId();
    if (empty($cart_id)) {
      // We get cases where cookies are not forwarded on redirects in mobile.
      $this->session->updateDataInSession(Cart::SESSION_STORAGE_KEY, (int) $data['cart_id']);
    }
    elseif ($data['cart_id'] != $cart_id) {
      $this->logger->error('3D secure payment came into @callback with cart not matching in session. Payment token: @token', [
        '@token' => $payment_token,
        '@callback' => $callback,
      ]);

      throw new \Exception('/' . $data['data']['langcode'] . '/checkout', 302);
    }

    $cart = $this->cart->getCart();
    if (empty($cart) || !empty($cart['error'])) {
      $this->logger->error('3D secure payment came into @callback but not able to load cart for the payment data. Cart id: @id, responseCode: @code, Payment token: @token', [
        '@id' => $data['cart_id'],
        '@code' => $data['responseCode'],
        '@token' => $payment_token,
        '@callback' => $callback,
      ]);

      throw new \Exception('/' . $data['data']['langcode'] . '/checkout', 302);
    }

    return $data;
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
      $this->logger->error('KNET @callback callback requested with cart not matching in session. Data: @message', [
        '@message' => json_encode($data),
        '@callback' => $callback,
      ]);

      throw new \Exception('/' . $data['data']['langcode'] . '/checkout', 302);
    }

    $cart = $this->cart->getCart();
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
