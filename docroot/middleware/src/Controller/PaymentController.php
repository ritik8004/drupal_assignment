<?php

namespace App\Controller;

use App\Helper\CookieHelper;
use App\Service\Cart;
use Psr\Log\LoggerInterface;
use App\Service\Config\SystemSettings;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PaymentController.
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
   * PaymentController constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   * @param \App\Service\Cart $cart
   *   Cart service.
   */
  public function __construct(LoggerInterface $logger,
                              SystemSettings $settings,
                              Cart $cart) {
    $this->logger = $logger;
    $this->settings = $settings;
    $this->cart = $cart;
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
    // Add success message in logs.
    $order_id = $request->query->get('order_id');
    $cart = $this->cart->getCart();
    $payment_method = $this->cart->getPaymentMethodSetOnCart();
    $this->logger->notice('Order placed successfully for Cart: @cart Payment Method: @payment_method Order-Id: @order_id', [
      '@cart' => json_encode($cart),
      '@payment_method' => $payment_method,
      '@order_id' => $order_id,
    ]);
    $langcode = $request->get('langcode');
    // In case of error, we redirect to cart page.
    $redirect = new RedirectResponse('/' . $langcode . '/cart', 302);

    try {
      // Post processing on success which involves cleaning cache and session.
      $order = $this->cart->processPostOrderPlaced($order_id, $payment_method);
      // Redirect user to confirmation page.
      $redirect->setTargetUrl('/' . $langcode . '/checkout/confirmation?id=' . $order['secure_order_id']);
      $redirect->headers->setCookie(CookieHelper::create('middleware_order_placed', 1, strtotime('+1 year')));
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
    $this->logger->error('Payment failed for Cart: @cart Payment Method: @payment_method', [
      '@cart' => json_encode($this->cart->getCart()),
      '@payment_method' => $this->cart->getPaymentMethodSetOnCart(),
    ]);

    $langcode = $request->get('langcode');
    $message = $request->query->get('message');
    $response = new RedirectResponse('/' . $langcode . '/checkout' . $message, 302);

    $payment_data = [
      'status' => self::PAYMENT_FAILED_VALUE,
      'payment_method' => $this->cart->getPaymentMethodSetOnCart(),
      'status' => self::PAYMENT_DECLINED_VALUE,
    ];

    $response->headers->setCookie(CookieHelper::create('middleware_payment_error', json_encode($payment_data), strtotime('+1 year')));

    return $response;
  }

}
