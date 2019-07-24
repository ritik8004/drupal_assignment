<?php

namespace Drupal\acq_checkoutcom\Controller;

use Drupal\acq_checkoutcom\CheckoutComFormHelper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApplePayController.
 *
 * @package Drupal\acq_checkoutcom\Controller
 */
class ApplePayController implements ContainerInjectionInterface {

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
      $container->get('acq_checkoutcom.form_helper'),
      $container->get('logger.factory')->get('ApplePayController')
    );
  }

  /**
   * ApplePayController constructor.
   *
   * @param \Drupal\acq_checkoutcom\CheckoutComFormHelper $form_helper
   *   Checkout.com form Helper.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(CheckoutComFormHelper $form_helper, LoggerInterface $logger) {
    $this->formHelper = $form_helper;
    $this->logger = $logger;
  }

  /**
   * Validate page callback.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   HTTP Request.
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

    if (curl_exec($ch) === FALSE) {
      $message = curl_error($ch);
      $this->logger->info('Failure while invoking apple.com api. @message', [
        '@message' => $message,
      ]);

      $return['curlError'] = curl_error($ch);
    }

    curl_close($ch);

    return new JsonResponse($return);
  }

}
