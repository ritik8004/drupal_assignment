<?php

namespace AlshayaMiddleware\Controller;

use AlshayaMiddleware\Magento\CartActions;
use AlshayaMiddleware\Magento\MagentoInfo;
use AlshayaMiddleware\Drupal\Drupal;
use AlshayaMiddleware\Magento\Cart;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CartController.
 */
class CartController {

  /**
   * Service for magento info.
   *
   * @var \AlshayaMiddleware\Magento\MagentoInfo
   */
  protected $magentoInfo;

  /**
   * Drupal service.
   *
   * @var \AlshayaMiddleware\Drupal\Drupal
   */
  protected $drupal;

  /**
   * Service for cart interaction.
   *
   * @var \AlshayaMiddleware\Magento\Cart
   */
  protected $cart;

  /**
   * CartController constructor.
   *
   * @param \AlshayaMiddleware\Magento\MagentoInfo $magentoInfo
   *   Magento info service.
   * @param \AlshayaMiddleware\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \AlshayaMiddleware\Magento\Cart $cart
   *   Cart service.
   */
  public function __construct(MagentoInfo $magentoInfo,
                              Drupal $drupal,
                              Cart $cart) {
    $this->magentoInfo = $magentoInfo;
    $this->drupal = $drupal;
    $this->cart = $cart;
  }

  /**
   * Get cart controller.
   *
   * @param \Silex\Application $app
   *   Silex application.
   * @param int $cart_id
   *   Cart id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function getCart(Application $app, int $cart_id) {
    $data = $this->cart->getCart($cart_id);

    // If there is any exception/error, return as is with exception message
    // without processing further.
    if (!empty($data['error'])) {
      return $app->json($data);
    }

    // Here we will do the processing of cart to make it in required format.
    $data = $this->getProcessedCartData($data);
    return $app->json($data);
  }

  /**
   * Update cart controller.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   * @param \Silex\Application $app
   *   Silex app.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   */
  public function updateCart(Request $request, Application $app) {
    // Validate request.
    if (!$this->validateRequest($request)) {
      // Return error response if not valid data.
      return $app->json($this->cart->getErrorResponse('Invalid data'), '500');
    }

    $action = $request->request->get('action');

    switch ($action) {
      case CartActions::CART_CREATE_NEW:
        // First create a new cart.
        $cart_id = $this->cart->createCart();
        // Then add item to the cart.
        $cart = $this->cart->addUpdateRemoveItem($cart_id, $request->request->get('sku'), $request->request->get('quantity'), CartActions::CART_ADD_ITEM);

        if (!empty($cart['error'])) {
          return $app->json($cart);
        }

        // Here we will do the processing of cart to make it in required format.
        $cart = $this->getProcessedCartData($cart);
        return $app->json($cart);

      case CartActions::CART_ADD_ITEM:
      case CartActions::CART_UPDATE_ITEM:
      case CartActions::CART_REMOVE_ITEM:
        $cart_id = $request->request->get('cart_id');
        $cart = $this->cart->addUpdateRemoveItem($cart_id, $request->request->get('sku'), $request->request->get('quantity'), $action);

        if (!empty($cart['error'])) {
          return $app->json($cart);
        }

        // Here we will do the processing of cart to make it in required format.
        $cart = $this->getProcessedCartData($cart);
        return $app->json($cart);

      case CartActions::CART_APPLY_COUPON:
      case CartActions::CART_REMOVE_COUPON:
        $cart_id = $action = $request->request->get('cart_id');
        $cart = $this->cart->applyRemovePromo($cart_id, $request->request->get('promo'), $action);

        if (!empty($cart['error'])) {
          return $app->json($cart);
        }

        // Here we will do the processing of cart to make it in required format.
        $cart = $this->getProcessedCartData($cart);
        return $app->json($cart);

    }
  }

  /**
   * Validate incoming request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return bool
   *   Valid request or not.
   */
  private function validateRequest(Request $request) {
    $valid = TRUE;

    // If action info or cart id not available.
    if (empty($request->request->get('action'))) {
      $valid = FALSE;
    }
    elseif ($request->request->get('action') != CartActions::CART_CREATE_NEW
      && empty($request->request->get('cart_id'))) {
      $valid = FALSE;
    }

    return $valid;
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

    $sku_items = array_column($cart_data['cart']['items'], 'sku');
    $items_quantity = array_column($cart_data['cart']['items'], 'qty', 'sku');
    $data['items'] = $this->drupal->getCartItemDrupalData($sku_items);
    foreach ($data['items'] as $key => $value) {
      if (isset($items_quantity[$key])) {
        $data['items'][$key]['qty'] = $items_quantity[$key];
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

}
