<?php

namespace App\Service\CheckoutCom;

use App\Service\Cart;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ApplePayHelper.
 *
 * @package App\Service\CheckoutCom
 */
class ApplePayHelper {

  /**
   * Cart service.
   *
   * @var \App\Service\Cart
   */
  protected $cart;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * ApplePayHelper constructor.
   *
   * @param \App\Service\Cart $cart
   *   Cart service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Current request stack.
   */
  public function __construct(Cart $cart, RequestStack $request) {
    $this->cart = $cart;
    $this->request = $request->getCurrentRequest();
  }

  /**
   * Push payment data in required format to Magento.
   *
   * @return array
   *   Status.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function updatePayment() {
    $params = $this->request->request->all();

    if (empty($params) || empty($params['paymentData'])) {
      throw new \Exception('paymentData missing');
    }

    $cart = $this->cart->getCart();

    if (empty($cart)) {
      throw new \Exception('No cart available');
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

    $response = $this->cart->updatePayment(
      [
        'method' => 'checkout_com_applepay',
        'additional_data' => $data,
      ],
      ['attempted_payment' => 1]
    );

    if (!empty($response['error'])) {
      throw new \Exception($response['error_message'], $response['error_code']);
    }

    return ['success' => TRUE];
  }

}
