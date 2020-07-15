<?php

namespace App\Controller;

use App\Helper\CookieHelper;
use App\Service\Cart;
use App\Service\CheckoutCom\APIWrapper;
use App\Service\CheckoutCom\ApplePayHelper;
use App\Service\PaymentData;
use App\Service\SessionStorage;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CheckoutComPaymentController.
 */
class CheckoutComPaymentController extends PaymentController {

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
   * Checkout.com Apple Pay Helper.
   *
   * @var \App\Service\CheckoutCom\ApplePayHelper
   */
  protected $applePayHelper;

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
   * Utility.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * CheckoutComPaymentController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\Cart $cart
   *   Service for cart interaction.
   * @param \App\Service\CheckoutCom\APIWrapper $checkout_com_api
   *   Checkout.com API Wrapper.
   * @param \App\Service\CheckoutCom\ApplePayHelper $apple_pay_helper
   *   Checkout.com Apple Pay helper.
   * @param \App\Service\PaymentData $payment_data
   *   Payment Data provider.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\SessionStorage $session
   *   Session Storage service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(
    RequestStack $request,
    Cart $cart,
    APIWrapper $checkout_com_api,
    ApplePayHelper $apple_pay_helper,
    PaymentData $payment_data,
    LoggerInterface $logger,
    SessionStorage $session,
    Utility $utility
  ) {
    $this->request = $request->getCurrentRequest();
    $this->cart = $cart;
    $this->checkoutComApi = $checkout_com_api;
    $this->applePayHelper = $apple_pay_helper;
    $this->paymentData = $payment_data;
    $this->logger = $logger;
    $this->session = $session;
    $this->utility = $utility;
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

    // Set the langcode for processing as request from checkout.com doesn't
    // contains language info.
    static::$externalPaymentLangcode = $data['data']['langcode'];

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

      return $this->handleCheckoutComError('3D secure payment came into success but responseCode was not success.');
    }

    $amount = $this->checkoutComApi->getCheckoutAmount($cart['totals']['base_grand_total'], $cart['totals']['quote_currency_code']);
    if (empty($charges['value']) || $charges['value'] != $amount) {
      $this->logger->error('3D secure payment came into success with proper responseCode but totals do not match. Cart id: @id, Amount in checkout: @value, Amount in Cart: @total', [
        '@id' => $cart['cart']['id'],
        '@value' => $charges['value'],
        '@total' => $amount,
      ]);

      return $this->handleCheckoutComError('3D secure payment came into success with proper responseCode but totals do not match.');
    }

    $response = new RedirectResponse('/' . $data['data']['langcode'] . '/checkout', 302);

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

      $response->setTargetUrl('/' . $data['data']['langcode'] . '/checkout/confirmation?id=' . $order['secure_order_id']);
      $response->headers->setCookie(CookieHelper::create('middleware_order_placed', '1', strtotime('+1 year')));

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
      $this->cart->cancelCartReservation($e->getMessage());
      $payment_data = [
        'status' => self::PAYMENT_FAILED_VALUE,
        'payment_method' => 'checkoutcom',
      ];
      $response->headers->setCookie(CookieHelper::create('middleware_payment_error', json_encode($payment_data), strtotime('+1 year')));
      $response->setTargetUrl('/' . $data['data']['langcode'] . '/checkout');
    }

    return $response;
  }

  /**
   * Handle checkout.com payment error callback.
   *
   * @param string|null $message
   *   The message to send with cancel cart reservation.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to cart or checkout page.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function handleCheckoutComError(string $message = NULL) {
    try {
      $this->cart->cancelCartReservation($message ? $message : '3d checkout.com request failed.');
      $data = $this->validateCheckoutComRequest('error');
    }
    catch (\Exception $e) {
      if ($e->getCode() === 302) {
        return new RedirectResponse($e->getMessage(), 302);
      }

      throw $e;
    }

    $response = new RedirectResponse('/' . $data['data']['langcode'] . '/checkout', 302);
    $payment_data = [
      'status' => self::PAYMENT_DECLINED_VALUE,
      'payment_method' => 'checkoutcom',
    ];
    $response->headers->setCookie(CookieHelper::create('middleware_payment_error', json_encode($payment_data), strtotime('+1 year')));
    return $response;
  }

  /**
   * Callback to save payment data for Apple Pay.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Success or error.
   */
  public function saveApplePayPayment() {
    try {
      $response = $this->applePayHelper->updatePayment();
    }
    catch (\Exception $e) {
      $this->logger->warning('Failed to save apple pay payment data, message: @message', [
        '@message' => $e->getMessage(),
      ]);
      $response = $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }

    return new JsonResponse($response);
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

}
