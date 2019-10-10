<?php

namespace AlshayaMiddleware\Controller;

use AlshayaMiddleware\Magento\CartActions;
use AlshayaMiddleware\Magento\MagentoInfo;
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
   * @param \AlshayaMiddleware\Magento\Cart $cart
   *   Cart service.
   */
  public function __construct(MagentoInfo $magentoInfo,
                              Cart $cart) {
    $this->magentoInfo = $magentoInfo;
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
        return $app->json($cart);

      case CartActions::CART_ADD_ITEM:
      case CartActions::CART_UPDATE_ITEM:
      case CartActions::CART_REMOVE_ITEM:
        $cart = $this->cart->addUpdateRemoveItem($cart_id, $request->request->get('sku'), $request->request->get('quantity'), $action);

        if (!empty($cart['error'])) {
          return $app->json($cart);
        }

        // Here we will do the processing of cart to make it in required format.
        return $app->json($cart);

      case CartActions::CART_APPLY_COUPON:
      case CartActions::CART_REMOVE_COUPON:
        $cart = $this->cart->applyRemovePromo($cart_id, $request->request->get('promo'), $action);

        if (!empty($cart['error'])) {
          return $app->json($cart);
        }

        // Here we will do the processing of cart to make it in required format.
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

}
