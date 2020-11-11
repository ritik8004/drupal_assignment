<?php

namespace App\Controller;

use App\Service\Cart;
use App\Service\Drupal\Drupal;
use App\Service\Magento\CartActions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * CartController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   RequestStack Object.
   * @param \App\Service\Cart $cart
   *   Cart service.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   */
  public function __construct(RequestStack $request,
                              Cart $cart,
                              Drupal $drupal) {
    $this->request = $request->getCurrentRequest();
    $this->cart = $cart;
    $this->drupal = $drupal;
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

    // Apply promo code.
    $cart = $this->cart->applyRemovePromo($promo_code, CartActions::CART_APPLY_COUPON);

    $url = sprintf('/rest/v1/product/%s', $sku);
    $response = $this->drupal->invokeApi('GET', $url);
    $result = $response->getBody()->getContents();
    $sku_data = json_decode($result, TRUE);
    $parent_sku = $sku_data['parent_sku'];
    $options = $sku_data['configurable_values'] ?? [];
    $quantity = 1;
    // Update cart with free gift.
    $updated_cart = $this->cart->addUpdateRemoveItem($sku, $quantity, CartActions::CART_ADD_ITEM, $options, null);

    return new JsonResponse($updated_cart);
  }

}
