<?php

namespace Drupal\acq_checkoutcom\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_checkoutcom\CheckoutComFormHelper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Apple Pay Controller.
 *
 * @package Drupal\acq_checkoutcom\Controller
 */
class ApplePayController implements ContainerInjectionInterface {

  /**
   * The cart storage.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Checkout.com form Helper.
   *
   * @var \Drupal\acq_checkoutcom\CheckoutComFormHelper
   */
  protected $formHelper;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_cart.cart_storage'),
      $container->get('acq_checkoutcom.form_helper'),
      $container->get('logger.factory')->get('ApplePayController')
    );
  }

  /**
   * ApplePayController constructor.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart storage.
   * @param \Drupal\acq_checkoutcom\CheckoutComFormHelper $form_helper
   *   Checkout.com form Helper.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(CartStorageInterface $cart_storage,
                              CheckoutComFormHelper $form_helper,
                              LoggerInterface $logger) {
    $this->cartStorage = $cart_storage;
    $this->formHelper = $form_helper;
    $this->logger = $logger;
  }

  /**
   * Validate page callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   HTTP Request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON Response.
   */
  public function validate(Request $request) {
    $return = [];

    // Get the validation URL from the request.
    $url = $request->query->get('u');

    // Sanity check for callback.
    if (parse_url($url, PHP_URL_SCHEME) != 'https' || substr(parse_url($url, PHP_URL_HOST), -10) != '.apple.com') {
      throw new \InvalidArgumentException();
    }

    $settings = $this->formHelper->getApplePayConfig();
    $settings += $this->formHelper->getApplePaySecretInfo();

    $ch = curl_init();

    $data = [
      'merchantIdentifier' => $settings['merchantIdentifier'],
      'domainName' => $_SERVER['HTTP_HOST'],
      'displayName' => $settings['storeName'],
    ];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSLCERT, $settings['merchantCertificatePem']);
    curl_setopt($ch, CURLOPT_SSLKEY, $settings['merchantCertificateKey']);
    curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $settings['merchantCertificatePass']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, Json::encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);

    if ($response === FALSE) {
      $message = curl_error($ch);
      $this->logger->info('Failure while invoking apple.com api. @message', [
        '@message' => $message,
      ]);

      $return['curlError'] = curl_error($ch);
    }
    else {
      $return = json_decode($response);
    }

    curl_close($ch);

    return new JsonResponse($return);
  }

  /**
   * Save payment info to cart.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   HTTP Request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON Response.
   */
  public function savePayment(Request $request) {
    $params = $request->request->all();

    if (empty($params) || empty($params['paymentData'])) {
      return new JsonResponse(['status' => FALSE]);
    }

    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      return new JsonResponse(['status' => FALSE]);
    }

    // Format data in 1D array.
    $data = [
      'data' => $params['paymentData']['data'],
      'ephemeralPublicKey' => $params['paymentData']['header']['ephemeralPublicKey'],
      'publicKeyHash' => $params['paymentData']['header']['publicKeyHash'],
      'transactionId' => $params['paymentData']['header']['transactionId'],
      'signature' => $params['paymentData']['signature'],
      'version' => $params['paymentData']['version'],
      'paymentMethodDisplayName' => $params['paymentMethod']['displayName'],
      'paymentMethodNetwork' => $params['paymentMethod']['network'],
      'paymentMethodType' => $params['paymentMethod']['type'],
      'transactionIdentifier' => $params['transactionIdentifier'],
    ];

    $cart->setPaymentMethod('checkout_com_applepay', $data);

    return new JsonResponse(['status' => TRUE]);
  }

}
