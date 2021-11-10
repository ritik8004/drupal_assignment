<?php

namespace App\Controller;

use App\Service\Cart;
use App\Service\Drupal\Drupal;
use App\Service\Magento\CartActions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;
use App\Service\Utility;

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
   * Utility.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

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
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(RequestStack $request,
                              Cart $cart,
                              Drupal $drupal,
                              LoggerInterface $logger,
                              Utility $utility) {
    $this->request = $request->getCurrentRequest();
    $this->cart = $cart;
    $this->drupal = $drupal;
    $this->logger = $logger;
    $this->utility = $utility;
  }

  /**
   * API to remove free gift item from cart.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   */
  public function selectFreeGift() {
    $data = json_decode($this->request->getContent(), TRUE);
    $sku = $data['sku'];
    $promo_code = $data['promo'];
    $langcode = $data['langcode'];
    $promo_rule_id = $data['promoRuleId'];
    $sku_type = $data['type'];

    if (empty($sku) || empty($promo_code) || empty($langcode)) {
      $this->logger->error('Missing request header parameters. SKU: @sku, Promo: @promo_code, Langcode: @langcode', [
        '@sku' => $sku,
        '@promo_code' => $promo_code,
        '@langcode' => $langcode,
      ]);
      $cart = $this->cart->getCart();
    }
    else {
      // Apply promo code.
      $cart = $this->cart->applyRemovePromo($promo_code, CartActions::CART_APPLY_COUPON);
      // Condition to check if cart is empty.
      if (empty($cart)) {
        $this->logger->error('Cart is empty. Cart: @cart', [
          '@cart' => json_encode($cart),
        ]);
      }
      // Condition to check valid promo code.
      else if (empty($cart['totals']['coupon_code'])) {
        $this->logger->error('Invalid promo code. Cart: @cart, Promo: @promo_code', [
          '@cart' => json_encode($cart),
          '@promo_code' => $promo_code,
        ]);
      }
      else {
        $quantity = 1;
        // Update cart with free gift.
        $options = $data['configurable_values'] ?? [];

        // If options data available.
        if (!empty($options)) {
          $option_data = [
            'extension_attributes' => [
              'configurable_item_options' => $options,
            ],
          ];
        }
        $variant = $data['variant'] ?? null;
        if ($sku_type == 'simple') {
          $data['items'][] = [
            'sku' => $data['sku'],
            'qty' => 1,
            'product_type' => $sku_type,
            'extension_attributes' => [
              'promo_rule_id' => $promo_rule_id
            ],
          ];
          $data['extension'] = (object) [
            'action' => 'add item',
          ];
        } else {
           $data['items'][] = [
              'sku' => $data['sku'],
              'qty' => 1,
              'product_type' => $sku_type,
              'product_option' => (object) $option_data,
              'variant_sku' => $variant,
              'extension_attributes' => [
               'promo_rule_id' => $promo_rule_id
              ],
            ];
            $data['extension'] = (object) [
              'action' => 'add item',
           ];
        }


        $updated_cart = $this->cart->updateCart($data);

        if (empty($updated_cart)) {
          $this->logger->error('Update cart failed. Cart: @cart', [
            '@cart' => json_encode($cart),
          ]);
        }
        else {
          $cart = $updated_cart;
        }
      }
    }

    $processed_cart_data = $this->cart->getProcessedCartData($cart, $langcode);
    return new JsonResponse($processed_cart_data);
  }

}
