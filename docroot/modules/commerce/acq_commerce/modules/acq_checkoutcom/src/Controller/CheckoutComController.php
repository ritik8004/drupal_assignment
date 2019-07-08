<?php

namespace Drupal\acq_checkoutcom\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CheckoutComController.
 *
 * @package Drupal\acq_checkoutcom\Controller
 */
class CheckoutComController implements ContainerInjectionInterface {

  use StringTranslationTrait;

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
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * CheckoutComController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   APIWrapper service object.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart storage.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    APIWrapper $api_wrapper,
    CartStorageInterface $cart_storage,
    LoggerInterface $logger
  ) {
    $this->apiVersion = $config_factory->get('acq_commerce.conductor')->get('api_version');
    $this->apiWrapper = $api_wrapper;
    $this->cartStorage = $cart_storage;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('acq_commerce.agent_api'),
      $container->get('acq_cart.cart_storage'),
      $container->get('logger.factory')->get('acq_checkoutcom')
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
    $payment_token = $request->query->get('cko-payment-token');
    try {
      // Push the additional data to cart.
      $cart = $this->cartStorage->getCart(FALSE);
      $cart->setPaymentMethod(
        'checkout_com',
        ['cko_payment_token' => $payment_token]
      );

      $this->cartStorage->updateCart(FALSE);

      // Place the order now.
      $this->apiWrapper->placeOrder($cart->id());

      // Add success message in logs.
      $this->logger->info('Placed order. Cart: @cart. Payment method @method.', [
        '@cart' => $this->cartHelper->getCleanCartToLog($cart),
        '@method' => 'checkout_com',
      ]);
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
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
   */
  public function fail(Request $request) {
    // @todo: Update fail logic here.
    // $payment_token = $request->query->get('cko-payment-token');
    $url = Url::fromRoute('acq_cart.cart', [])->toString();
    return new RedirectResponse($url, 302);
  }

}
