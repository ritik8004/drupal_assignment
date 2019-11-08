<?php

namespace App\Controller;

use App\Service\Magento\CartActions;
use App\Service\Cart;
use App\Service\Drupal\Drupal;
use App\Service\Magento\MagentoInfo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CartController.
 */
class CartController {

  /**
   * Service for magento info.
   *
   * @var \App\Service\Magento\MagentoInfo
   */
  protected $magentoInfo;

  /**
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * Service for cart interaction.
   *
   * @var \App\Service\Cart
   */
  protected $cart;

  /**
   * CartController constructor.
   *
   * @param \App\Service\Cart $cart
   *   Cart service.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   */
  public function __construct(Cart $cart, Drupal $drupal, MagentoInfo $magento_info) {
    $this->cart = $cart;
    $this->drupal = $drupal;
    $this->magentoInfo = $magento_info;
  }

  /**
   * Get cart data.
   *
   * @param int $cart_id
   *   Cart id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Cart response.
   */
  public function getCart(int $cart_id) {
    $data = $this->cart->getCart($cart_id);

    // If there is any exception/error, return as is with exception message
    // without processing further.
    if (!empty($data['error'])) {
      return new JsonResponse($data);
    }

    // Here we will do the processing of cart to make it in required format.
    $data = $this->getProcessedCartData($data);
    return new JsonResponse($data);
  }

  /**
   * Process cart data.
   *
   * @param array $cart_data
   *   Cart data.
   *
   * @return array
   *   Processed data.
   */
  private function getProcessedCartData(array $cart_data) {
    $data = [];
    $data['cart_id'] = $cart_data['cart']['id'];
    $data['items_qty'] = $cart_data['cart']['items_qty'];
    $data['cart_total'] = $cart_data['totals']['base_grand_total'];
    $data['totals'] = [
      'subtotal_incl_tax' => $cart_data['totals']['subtotal_incl_tax'],
      'base_grand_total' => $cart_data['totals']['base_grand_total'],
      'discount_amount' => $cart_data['totals']['discount_amount'],
    ];

    $data['coupon_code'] = $cart_data['totals']['coupon_code'] ?? '';

    // Set the status message if we get from magento.
    if (!empty($cart_data['response_message'])) {
      $data['response_message'] = [
        'status' => $cart_data['response_message'][1],
        'msg' => $cart_data['response_message'][0],
      ];
    }

    // For determining global OOS for cart.
    $data['in_stock'] = TRUE;

    $sku_items = array_column($cart_data['cart']['items'], 'sku');
    $items_quantity = array_column($cart_data['cart']['items'], 'qty', 'sku');
    $data['items'] = $this->drupal->getCartItemDrupalData($sku_items);
    foreach ($data['items'] as $key => $value) {
      if (isset($items_quantity[$key])) {
        $data['items'][$key]['qty'] = $items_quantity[$key];
      }

      // For the OOS.
      if ($data['in_stock'] && !$value['in_stock']) {
        $data['in_stock'] = FALSE;
      }
    }

    // Prepare recommended product data.
    $recommended_products = $this->drupal->getDrupalLinkedSkus($sku_items);
    $recommended_products_data = [];
    // If there any recommended products.
    if (!empty($recommended_products)) {
      foreach ($recommended_products as $recommended_product) {
        if (!empty($recommended_product['linked'])) {
          foreach ($recommended_product['linked'] as $linked) {
            if ($linked['link_type'] == 'crosssell' && !empty($linked['skus'])) {
              foreach ($linked['skus'] as $link) {
                $recommended_products_data[$link['sku']] = $link;
              }
            }
          }
        }
      }
      $data['recommended_products'] = $recommended_products_data;
    }

    return $data;
  }

  /**
   * Update cart controller.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function updateCart(Request $request) {

    $request_content = json_decode($request->getContent(), TRUE);

    // Validate request.
    if (!$this->validateRequestData($request_content)) {
      // Return error response if not valid data.
      return new JsonResponse($this->cart->getErrorResponse('Invalid data', '500'));
    }

    $action = $request_content['action'];

    switch ($action) {
      case CartActions::CART_CREATE_NEW:
        // First create a new cart.
        $cart_id = $this->cart->createCart();
        // Then add item to the cart.
        $cart = $this->cart->addUpdateRemoveItem($cart_id, $request_content['sku'], $request_content['quantity'], CartActions::CART_ADD_ITEM);

        if (!empty($cart['error'])) {
          return new JsonResponse($cart);
        }

        // Here we will do the processing of cart to make it in required format.
        $cart = $this->getProcessedCartData($cart);
        return new JsonResponse($cart);

      case CartActions::CART_ADD_ITEM:
      case CartActions::CART_UPDATE_ITEM:
      case CartActions::CART_REMOVE_ITEM:
        $cart_id = $request_content['cart_id'];
        $cart = $this->cart->addUpdateRemoveItem($cart_id, $request_content['sku'], $request_content['quantity'], $action);

        if (!empty($cart['error'])) {
          return new JsonResponse($cart);
        }

        // Here we will do the processing of cart to make it in required format.
        $cart = $this->getProcessedCartData($cart);
        return new JsonResponse($cart);

      case CartActions::CART_APPLY_COUPON:
      case CartActions::CART_REMOVE_COUPON:
        $cart_id = $request_content['cart_id'];
        $cart = $this->cart->applyRemovePromo($cart_id, $request_content['promo'], $action);

        if (!empty($cart['error'])) {
          return new JsonResponse($cart);
        }

        // Here we will do the processing of cart to make it in required format.
        $cart = $this->getProcessedCartData($cart);
        return new JsonResponse($cart);
    }
  }

  /**
   * Validate incoming request.
   *
   * @param array $request_content
   *   Request data.
   *
   * @return bool
   *   Valid request or not.
   */
  private function validateRequestData(array $request_content) {
    $valid = TRUE;

    // If action info or cart id not available.
    if (empty($request_content['action'])) {
      $valid = FALSE;
    }
    elseif ($request_content['action'] != CartActions::CART_CREATE_NEW
            && empty($request_content['cart_id'])) {
      $valid = FALSE;
    }

    return $valid;
  }

}
