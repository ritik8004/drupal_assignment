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
    $request_content = json_decode($this->request->getContent(), TRUE);
    $data = json_decode($request_content['data'], TRUE);
    $sku = $data['sku'];
    $promo_code = $data['promo'];
    $options = $data['configurable_values'];
    $variant = $data['variant'];
    $langcode = $data['langcode'];

    if (empty($sku) || empty($promo_code)) {
      $this->logger->error('Missing request header parameters. SKU: @sku, Promo: @promo_code', [
        '@sku' => $sku,
        '@promo_code' => $promo_code,
      ]);
      $cart = $this->cart->getCart();
      
      $processed_cart_data = $this->getProcessedCartData($cart, $langcode);
      return new JsonResponse($processed_cart_data);
    }

    // Apply promo code.
    $cart = $this->cart->applyRemovePromo($promo_code, CartActions::CART_APPLY_COUPON);
    // Condition to check if cart is empty.
    if (empty($cart)) {
      $this->logger->error('Cart is empty. Cart: @cart', [
        '@cart' => json_encode($cart),
      ]);

      $processed_cart_data = $this->getProcessedCartData($cart, $langcode);
      return new JsonResponse($processed_cart_data);
    }
    // Condition to check valid promo code.
    $json_decoded_cart = json_decode($cart, TRUE);
    if (empty($cart['totals']['coupon_code'])) {
      $this->logger->error('Invalid promo code. Cart: @cart, Promo: @promo_code', [
        '@cart' => json_encode($cart),
        '@promo_code' => $promo_code,
      ]);
      $processed_cart_data = $this->getProcessedCartData($cart, $langcode);
      return new JsonResponse($processed_cart_data);
    }

    $quantity = 1;
    // Update cart with free gift.
    $updated_cart = $this->cart->addUpdateRemoveItem($sku, $quantity, CartActions::CART_ADD_ITEM, [], null);
    $processed_cart_data = $this->getProcessedCartData($updated_cart, $langcode);

    return new JsonResponse($processed_cart_data);
  }

  /**
   * Process cart data.
   *
   * @param array $cart_data
   *   Cart data.
   * @param string $langcode
   *   Langcode.
   *
   * @return array
   *   Processed data.
   */
  private function getProcessedCartData(array $cart_data, string $langcode) {
    $data = [];

    $data['cart_id'] = $cart_data['cart']['id'];
    $data['uid'] = $this->getDrupalInfo('uid') ?: 0;
    $data['langcode'] = $langcode;
    $data['customer'] = $cart_data['customer'] ?? NULL;

    $data['coupon_code'] = $cart_data['totals']['coupon_code'] ?? '';
    $data['appliedRules'] = $cart_data['cart']['applied_rule_ids'] ?? [];

    $data['items_qty'] = $cart_data['cart']['items_qty'];
    $data['cart_total'] = $cart_data['totals']['base_grand_total'] ?? 0;
    $data['minicart_total'] = $data['cart_total'];
    $data['surcharge'] = $cart_data['cart']['extension_attributes']['surcharge'] ?? [];
    $data['totals'] = [
      'subtotal_incl_tax' => $cart_data['totals']['subtotal_incl_tax'] ?? 0,
      'base_grand_total' => $cart_data['totals']['base_grand_total'] ?? 0,
      'base_grand_total_without_surcharge' => $cart_data['totals']['base_grand_total'] ?? 0,
      'discount_amount' => $cart_data['totals']['discount_amount'] ?? 0,
      'surcharge' => 0,
    ];

    if (empty($cart_data['shipping']) || empty($cart_data['shipping']['method'])) {
      // We use null to show "Excluding Delivery".
      $data['totals']['shipping_incl_tax'] = NULL;
    }
    elseif ($cart_data['shipping']['type'] !== 'click_and_collect') {
      // For click_n_collect we don't want to show this line at all.
      $data['totals']['shipping_incl_tax'] = $cart_data['totals']['shipping_incl_tax'] ?? 0;
    }

    if (is_array($data['surcharge']) && !empty($data['surcharge']) && $data['surcharge']['amount'] > 0 && $data['surcharge']['is_applied']) {
      $data['totals']['surcharge'] = $data['surcharge']['amount'];
    }

    // We don't show surcharge amount on cart total and on mini cart.
    if ($data['totals']['surcharge'] > 0) {
      $data['totals']['base_grand_total_without_surcharge'] -= $data['totals']['surcharge'];
      $data['minicart_total'] -= $data['totals']['surcharge'];
    }

    $data['response_message'] = NULL;
    // Set the status message if we get from magento.
    if (!empty($cart_data['response_message'])) {
      $data['response_message'] = [
        'status' => $cart_data['response_message'][1],
        'msg' => $cart_data['response_message'][0],
      ];
    }

    // For determining global OOS for cart.
    $data['in_stock'] = TRUE;
    // If there are any error at cart item level.
    $data['is_error'] = FALSE;

    try {
      $data['items'] = [];
      foreach ($cart_data['cart']['items'] as $item) {
        $data['items'][$item['sku']]['title'] = $item['name'];
        $data['items'][$item['sku']]['qty'] = $item['qty'];
        $data['items'][$item['sku']]['price'] = $item['price'];
        $data['items'][$item['sku']]['sku'] = $item['sku'];
        $data['items'][$item['sku']]['id'] = $item['item_id'];
        if (isset($item['extension_attributes'], $item['extension_attributes']['error_message'])) {
          $data['items'][$item['sku']]['error_msg'] = $item['extension_attributes']['error_message'];
          $data['is_error'] = TRUE;
        }

        // This is to determine whether item to be shown free or not in cart.
        $data['items'][$item['sku']]['freeItem'] = FALSE;
        foreach ($cart_data['totals']['items'] as $total_item) {
          // If total price of item matches discount, we mark as free.
          if ($item['item_id'] == $total_item['item_id']) {
            // Final price to use.
            $data['items'][$item['sku']]['finalPrice'] = $total_item['price_incl_tax'];

            // Free Item is only for free gift products which are having
            // price 0, rest all are free but still via different rules.
            if ($total_item['price_incl_tax'] == 0
                && isset($total_item['extension_attributes'], $total_item['extension_attributes']['amasty_promo'])) {
              $data['items'][$item['sku']]['freeItem'] = TRUE;
            }
            break;
          }
        }

        // Get stock data.
        $stockInfo = $this->drupal->getCartItemDrupalStock($item['sku']);
        $data['items'][$item['sku']]['in_stock'] = $stockInfo['in_stock'];
        $data['items'][$item['sku']]['stock'] = $stockInfo['stock'];

        // If info is available in static array, means this we get from
        // the cart update operation. We use that.
        if (!empty(Cart::$stockInfo)
          && isset(Cart::$stockInfo[$item['sku']])
          && !Cart::$stockInfo[$item['sku']]) {
          $data['items'][$item['sku']]['in_stock'] = FALSE;
          $data['items'][$item['sku']]['stock'] = 0;
        }

        // If any item is OOS.
        if (!$data['items'][$item['sku']]['in_stock'] || $data['items'][$item['sku']]['stock'] == 0) {
          $data['in_stock'] = FALSE;
        }
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Error while processing cart data. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }

    // Whether cart is stale or not.
    $data['stale_cart'] = $cart_data['stale_cart'] ?? FALSE;

    return $data;
  }

  /**
   * Return user id from current session.
   *
   * @return int|null
   *   Return user id or null.
   */
  protected function getDrupalInfo(string $key) {
    static $info = NULL;

    if (empty($info)) {
      $info = $this->drupal->getSessionCustomerInfo();
    }

    return $info[$key] ?? NULL;
  }

}
