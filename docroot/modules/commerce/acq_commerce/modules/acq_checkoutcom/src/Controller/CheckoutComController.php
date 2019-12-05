<?php

namespace Drupal\acq_checkoutcom\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_checkout\Event\AcqCheckoutPaymentFailedEvent;
use Drupal\acq_checkoutcom\CheckoutComAPIWrapper;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CheckoutComController.
 *
 * @package Drupal\acq_checkoutcom\Controller
 */
class CheckoutComController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * The cart storage.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Checkout.com api wrapper object.
   *
   * @var \Drupal\acq_checkoutcom\CheckoutComAPIWrapper
   */
  protected $checkoutComApi;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * CheckoutComController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   APIWrapper service object.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart storage.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   * @param \Drupal\acq_checkoutcom\CheckoutComAPIWrapper $checkout_com_Api
   *   Checkout.com api wrapper object.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              APIWrapper $api_wrapper,
                              CartStorageInterface $cart_storage,
                              MessengerInterface $messenger,
                              CheckoutComAPIWrapper $checkout_com_Api,
                              LoggerInterface $logger,
                              EventDispatcherInterface $dispatcher) {
    $this->configFactory = $config_factory;
    $this->apiVersion = $config_factory->get('acq_commerce.conductor')->get('api_version');
    $this->apiWrapper = $api_wrapper;
    $this->cartStorage = $cart_storage;
    $this->logger = $logger;
    $this->messenger = $messenger;
    $this->checkoutComApi = $checkout_com_Api;
    $this->dispatcher = $dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('acq_commerce.agent_api'),
      $container->get('acq_cart.cart_storage'),
      $container->get('messenger'),
      $container->get('acq_checkoutcom.checkout_api'),
      $container->get('logger.factory')->get('acq_checkoutcom'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * Page callback to process checkout.com response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response object.
   */
  public function success(Request $request) {
    $payment_token = $request->query->get('cko-payment-token') ?? '';
    $data = $this->getTokenData($payment_token);
    $cart = $this->cartStorage->getCart(FALSE);

    // Validate again.
    if (empty($data['responseCode']) || $data['responseCode'] != CheckoutComAPIWrapper::SUCCESS) {
      $this->logger->critical('3D secure payment came into success but responseCode was not success. Cart id: @id, responseCode: @code, Payment token: @token', [
        '@id' => $cart->id(),
        '@code' => $data['responseCode'],
        '@token' => $payment_token,
      ]);

      return $this->fail($request);
    }

    $amount = $this->checkoutComApi->getCheckoutAmount($cart->totals()['grand'] ?? 0);
    if (empty($data['value']) || $data['value'] != $amount) {
      $this->logger->critical('3D secure payment came into success with proper responseCode but totals do not match. Cart id: @id, Amount in checkout: @value, Amount in Cart: @total', [
        '@id' => $cart->id(),
        '@value' => $data['value'],
        '@total' => $amount,
      ]);

      return $this->fail($request);
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
   * Page callback to process checkout.com response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response object.
   *
   * @throws \Exception
   */
  public function fail(Request $request) {
    $payment_token = $request->query->get('cko-payment-token') ?? '';

    $data = $this->getTokenData($payment_token);
    unset($data['card'], $data['shippingDetails'], $data['billingDetails']);

    $cart = $this->cartStorage->getCart(FALSE);
    $this->logger->warning(
      'transactions failed for cart: @cart_id, order: @order and payment_token: @token. more info available here: @info',
      [
        '@cart_id' => $cart->id(),
        '@order' => $data['trackId'],
        '@token' => $payment_token,
        '@info' => Json::encode($data),
      ]
    );

    $event = new AcqCheckoutPaymentFailedEvent('checkout_com_applepay', 'Invalid data in payload or empty publicKeyHash.');
    $this->dispatcher->dispatch(AcqCheckoutPaymentFailedEvent::EVENT_NAME, $event);

    $this->checkoutComApi->setGenericError();
    $url = Url::fromRoute('acq_checkout.form', ['step' => 'payment'])->toString();
    return new RedirectResponse($url, 302);
  }

  /**
   * Validate and return data from checkout.com for token.
   *
   * @param mixed $payment_token
   *   Payment token.
   *
   * @return array
   *   Data for token from checkout.com.
   *
   * @throws \Exception
   */
  private function getTokenData($payment_token) {
    if (empty($payment_token)) {
      throw new NotFoundHttpException();
    }

    // Log payment token response.
    $data = $this->checkoutComApi->getChargesInfo($payment_token);

    if (empty($data)) {
      $this->logger->warning('User shown 404 page as checkout.com returned no data for the token.');
      throw new NotFoundHttpException();
    }

    // Validate cart too.
    $cart = $this->cartStorage->getCart(FALSE);
    if (empty($cart)) {
      $this->logger->warning('User shown 404 page as no cart available in session.');
      throw new NotFoundHttpException();
    }

    return $data;
  }

}
