<?php

namespace App\Controller;

use App\Service\Cart;
use App\Service\Drupal\Drupal;
use App\Service\Magento\CartActions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;

/**
 * Class Free Gift Controller.
 */
class FreeGiftController {

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
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * CartController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\Cart $cart
   *   Cart service.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   */
  public function __construct(RequestStack $request,
                              Cart $cart,
                              Drupal $drupal,
                              LoggerInterface $logger) {
    $this->request = $request->getCurrentRequest();
    $this->cart = $cart;
    $this->drupal = $drupal;
    $this->logger = $logger;
  }

  /**
   * API to allow select free gift item from Drupal.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   */
  public function selectFreeGift() {
    $sku = $this->request->headers->get('sku');
    $promo_code = $this->request->headers->get('promo');

    if (empty($sku) || empty($promo_code)) {
      $this->logger->error('Missing request header parameters. SKU: @sku, Promo: @promo_code', [
        '@sku' => $sku,
        '@promo_code' => $promo_code,
      ]);
      $cart = $this->cart->getCart();

      return new JsonResponse($cart);
    }

    // Apply promo code.
    $cart = $this->cart->applyRemovePromo($promo_code, CartActions::CART_APPLY_COUPON);
    // Condition to check if cart is empty.
    if (empty($cart)) {
      $this->logger->error('Cart is empty. Cart: @cart', [
        '@cart' => json_encode($cart),
      ]);

      return new JsonResponse($cart);
    }
    // Condition to check valid promo code.
    $json_decoded_cart = json_decode($cart, TRUE);
    if (empty($cart['totals']['coupon_code'])) {
      $this->logger->error('Invalid promo code. Cart: @cart, Promo: @promo_code', [
        '@cart' => json_encode($cart),
        '@promo_code' => $promo_code,
      ]);

      return new JsonResponse($cart);
    }

    $url = sprintf('/rest/v1/product/%s', $sku);
    $response = $this->drupal->invokeApi('GET', $url);
    $result = $response->getBody()->getContents();
    $sku_data = json_decode($result, TRUE);
    $options = $sku_data['configurable_values'] ?? [];
    $quantity = 1;
    // Update cart with free gift.
    $updated_cart = $this->cart->addUpdateRemoveItem($sku, $quantity, CartActions::CART_ADD_ITEM, $options, null);

    return new JsonResponse($updated_cart);
  }

}
