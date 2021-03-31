<?php

namespace App\Controller;

use App\Helper\CookieHelper;
use App\Service\Cart;
use App\Service\CheckoutCom\APIWrapper;
use Psr\Log\LoggerInterface;
use App\Service\Config\SystemSettings;
use App\Service\Orders;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * PaymentController Class for UPAPI callbacks.
 */
class PaymentController {

  /**
   * Value to set in cookie when payment is declined.
   */
  const PAYMENT_DECLINED_VALUE = 'declined';

  /**
   * Value to set in cookie for payment or place order failure.
   */
  const PAYMENT_FAILED_VALUE = 'failed';

  /**
   * Value to set in cookie for payment or place order failure.
   */
  const PLACE_ORDER_FAILED_VALUE = 'place_order_failed';

  /**
   * Langcode used for external payments like K-Net/Checkout.com.
   *
   * @var string|null
   */
  public static $externalPaymentLangcode = NULL;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

  /**
   * Service for cart interaction.
   *
   * @var \App\Service\Cart
   */
  protected $cart;

  /**
   * Order service.
   *
   * @var \App\Service\Orders
   */
  protected $order;

  /**
   * PaymentController constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   * @param \App\Service\Cart $cart
   *   Cart service.
   * @param \App\Service\Orders $order
   *   Order service.
   */
  public function __construct(LoggerInterface $logger,
                              SystemSettings $settings,
                              Cart $cart,
                              Orders $order) {
    $this->logger = $logger;
    $this->settings = $settings;
    $this->cart = $cart;
    $this->order = $order;
  }

  /**
   * Get payment failed status.
   *
   * @param string|int $exception_code
   *   Exception code.
   *
   * @return string
   *   Failure status.
   */
  protected function getPaymentFailedStatus($exception_code) {
    $status = self::PAYMENT_FAILED_VALUE;

    // When backend is down and configured to show different message.
    if ($exception_code >= 600 && $this->settings->getSettings('place_order_debug_failure', 1)) {
      $status = self::PLACE_ORDER_FAILED_VALUE;
    }

    return $status;
  }

  /**
   * Payment success callback.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response on success.
   */
  public function handlePaymentSuccess(Request $request) {
    $order_id = $request->query->get('order_id');
    $langcode = $request->get('langcode');
    // In case of error, we redirect to cart page.
    $redirect = new RedirectResponse('/' . $langcode . '/cart', 302);

    // If order id is not available in request.
    if (!$order_id) {
      $this->logger->error('User trying to access success url directly. Order-Id is not available in request.');
      return $redirect;
    }

    // If Cart is not available in session.
    $cart_id = $this->cart->getCartId();
    if (!$cart_id) {
      $this->logger->error('User trying to access success url directly. Cart is not available for the user.');
      return $redirect;
    }

    $cart = $this->cart->getCart();

    // Check and get order details from MDC.
    $order = $this->order->getOrderById($order_id);
    // If order not available in magento.
    if (!$order) {
      $this->logger->error('User is trying to access success url directly as order is not available for the orderId: @order_id.', [
        '@order_id' => $order_id,
      ]);
      return $redirect;
    }

    $payment_method = $order['payment']['method'];

    // If payment is done by saved cards.
    if ($payment_method === 'checkout_com_upapi'
      && !empty($order['payment']['extension_attributes'])
      && !empty($order['payment']['extension_attributes']['vault_payment_token'])) {
      $payment_method = APIWrapper::CHECKOUT_COM_UPAPI_VAULT_METHOD;
    }

    // If Payment-method is not selected by user.
    if (!$payment_method) {
      $this->logger->error('User trying to access success url directly. Payment method is not set on cart. Order-Id: @order_id Cart: @cart', [
        '@order_id' => $order_id,
        '@cart' => json_encode($cart),
      ]);
      return $redirect;
    }

    try {
      // Post processing on success which involves cleaning cache and session.
      $order = $this->cart->processPostOrderPlaced($order_id, $payment_method);
      // Redirect user to confirmation page.
      $redirect->setTargetUrl('/' . $langcode . '/checkout/confirmation?id=' . $order['secure_order_id']);
      $redirect->headers->setCookie(CookieHelper::create('middleware_order_placed', 1, strtotime('+1 year')));
      $this->logger->notice('Order placed successfully for Cart: @cart Payment Method: @payment_method Order-Id: @order_id', [
        '@cart' => json_encode($cart),
        '@payment_method' => $payment_method,
        '@order_id' => $order_id,
      ]);
    }
    catch (\Exception $e) {
      // If any error/exception encountered while order was placed from
      // magento side, we redirect to cart page.
      $this->logger->error('Error while order post processing. Cart: @cart Payment Method: @payment_method Order-Id: @order_id', [
        '@cart' => json_encode($cart),
        '@payment_method' => $payment_method,
        '@order_id' => $order_id,
      ]);
    }

    return $redirect;
  }

  /**
   * Payment failure callback.
   *
   * Failure callback that will be called from MDC.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response on failure.
   */
  public function handlePaymentFail(Request $request) {
    $langcode = $request->get('langcode');
    // In case of error, we redirect to cart page.
    $redirect = new RedirectResponse('/' . $langcode . '/cart', 302);

    // If Cart is not available in session.
    $cart_id = $this->cart->getCartId();
    if (!$cart_id) {
      $this->logger->error('User trying to access fail url directly. Cart is not available for the user.');
      return $redirect;
    }

    $cart = $this->cart->getCart();
    $payment_method = $this->cart->getPaymentMethodSetOnCart();

    // If payment method is not string but array.
    if (is_array($payment_method)) {
      $this->logger->error('Payment method could not found. Detail: @details', [
        '@details' => json_encode($payment_method),
      ]);
      $payment_method = 'NOT_FOUND';
    }

    // If Payment-method is not selected by user.
    if (!$payment_method) {
      $this->logger->error('User trying to access fail url directly. Payment method is not set on cart. Cart: @cart', [
        '@cart' => json_encode($cart),
      ]);
      return $redirect;
    }

    $this->logger->error('Payment failed for Cart: @cart Payment Method: @payment_method', [
      '@cart' => json_encode($this->cart->getCart()),
      '@payment_method' => $payment_method,
    ]);

    $response = new RedirectResponse('/' . $langcode . '/checkout', 302);

    $payment_data = [
      'status' => self::PAYMENT_DECLINED_VALUE,
      'payment_method' => $payment_method,
      'message' => $request->query->get('message'),
    ];

    switch ($request->query->get('type')) {
      case 'qpay';
        $payment_data['data'] = [
          'transaction_id' => $request->query->get('confirmation_id', ''),
          'payment_id' => $request->query->get('pun', ''),
          'result_code' => $request->query->get('status_message', $request->query->get('status', '')),
          'amount' => $request->query->get('amount', $cart['totals']['grand_total']),
          'date' => $request->query->get('requested_on', ''),
        ];
        break;
    }

    $response->headers->setCookie(CookieHelper::create('middleware_payment_error', json_encode($payment_data), strtotime('+1 year')));

    return $response;
  }

}
