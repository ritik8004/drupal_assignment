<?php

namespace App\Controller;

use App\Service\Drupal\Drupal;
use App\Service\Magento\MagentoInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
   * Service for magento info.
   *
   * @var \App\Service\Magento\MagentoInfo
   */
  protected $magentoInfo;

  /**
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * Service for cart interaction.
   *
   * @var \App\Service\Cart
   */
  protected $cart;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Service for session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * PaymentController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   Service for session.
   */
  public function __construct(RequestStack $request, Drupal $drupal, MagentoInfo $magento_info, LoggerInterface $logger, SessionInterface $session) {
    $this->request = $request->getCurrentRequest();
    $this->drupal = $drupal;
    $this->magentoInfo = $magento_info;
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
    $payment_token = $this->request->query->get('cko-payment-token') ?? '';
    $data = $this->getTokenData($payment_token);
    $cart = $this->cartStorage->getCart(FALSE);

    // Validate again.
    if (empty($data['responseCode']) || $data['responseCode'] != CheckoutComAPIWrapper::SUCCESS) {
      $this->logger->critical('3D secure payment came into success but responseCode was not success. Cart id: @id, responseCode: @code, Payment token: @token', [
        '@id' => $cart->id(),
        '@code' => $data['responseCode'],
        '@token' => $payment_token,
      ]);

      return $this->handleCheckoutComFailure();
    }

    $amount = $this->checkoutComApi->getCheckoutAmount($cart->totals()['grand'] ?? 0);
    if (empty($data['value']) || $data['value'] != $amount) {
      $this->logger->critical('3D secure payment came into success with proper responseCode but totals do not match. Cart id: @id, Amount in checkout: @value, Amount in Cart: @total', [
        '@id' => $cart->id(),
        '@value' => $data['value'],
        '@total' => $amount,
      ]);

      return $this->handleCheckoutComFailure();
    }

    try {
      // Push the additional data to cart.
      $cart->setPaymentMethod(
        'checkout_com',
        ['cko_payment_token' => $payment_token]
      );

      $this->cartStorage->updateCart(FALSE);

      // Place the order now.
      $this->apiWrapper->placeOrder($cart->id());

      // Add success message in logs.
      $this->logger->info('Placed order. Cart: @cart. Payment method @method.', [
        '@cart' => $cart->getDataToLog(),
        '@method' => 'checkout_com',
      ]);
    }
    catch (\Exception $e) {
      $this->logger->error(
        'Failed to place order for cart @cart_id with message: @message',
        ['@cart_id' => $cart->id(), '@message' => $e->getMessage()]
      );
      $this->messenger->addError(
        $this->t('An error occurred while placing your order. Please contact our customer service team for assistance.')
      );
      $url = Url::fromRoute('acq_checkout.form', ['step' => 'payment'])->toString();
      return new RedirectResponse($url, 302);
    }

    $url = Url::fromRoute('acq_checkout.form', ['step' => 'confirmation'])->toString();
    return new RedirectResponse($url, 302);

  }

  /**
   * Handle checkout.com payment error callback.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to cart or checkout page.
   */
  public function handleCheckoutComFailure() {
    return new RedirectResponse('', 302);
  }

}
