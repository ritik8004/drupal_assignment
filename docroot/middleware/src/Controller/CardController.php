<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\CartSession;
use App\Service\CheckoutCom\CustomerCards;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CardController.
 */
class CardController {

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Checkout.com API Wrapper.
   *
   * @var \App\Service\CheckoutCom\APIWrapper
   */
  protected $customerCards;

  /**
   * Payment Data provider.
   *
   * @var \App\Service\PaymentData
   */
  protected $cartSession;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * CardController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\CheckoutCom\CustomerCards $customer_cards
   *   Checkout.com API Wrapper.
   * @param \App\Service\CartSession $cart_session
   *   Payment Data provider.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   */
  public function __construct(
    RequestStack $request,
    CustomerCards $customer_cards,
    CartSession $cart_session,
    LoggerInterface $logger
  ) {
    $this->request = $request->getCurrentRequest();
    $this->customerCards = $customer_cards;
    $this->cartSession = $cart_session;
    $this->logger = $logger;
  }

  /**
   * Get current customer cards.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The array of cards.
   */
  public function getCards() {
    $customer_id = $this->cartSession->getSessionCustomerId();
    $cards = $this->customerCards->getCustomerCards($customer_id);
    return new JsonResponse($cards ?? []);
  }

}
