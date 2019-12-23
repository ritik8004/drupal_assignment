<?php

namespace App\Controller;

use App\Service\Magento\CartActions;
use App\Service\Cart;
use App\Service\Drupal\Drupal;
use App\Service\Magento\MagentoInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class CartController.
 */
class CartController {

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

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
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   */
  public function __construct(RequestStack $request, Cart $cart, Drupal $drupal, MagentoInfo $magento_info, LoggerInterface $logger) {
    $this->request = $request->getCurrentRequest();
    $this->cart = $cart;
    $this->drupal = $drupal;
    $this->magentoInfo = $magento_info;
    $this->logger = $logger;
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
      $this->logger->error('Error while getting cart:{cart_id} Error:{error}', [
        'cart_id' => $cart_id,
        'error' => json_encode($data),
      ]);
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
    $data['langcode'] = $this->request->query->get('lang', 'en');
    $data['cart_id'] = $cart_data['cart']['id'];
    $data['items_qty'] = $cart_data['cart']['items_qty'];
    $data['cart_total'] = $cart_data['totals']['base_grand_total'];
    $data['totals'] = [
      'subtotal_incl_tax' => $cart_data['totals']['subtotal_incl_tax'],
      'base_grand_total' => $cart_data['totals']['base_grand_total'],
      'discount_amount' => $cart_data['totals']['discount_amount'],
      'free_delivery' => FALSE,
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
    $items_id = array_column($cart_data['cart']['items'], 'item_id', 'sku');
    $data['items'] = $this->drupal->getCartItemDrupalData($sku_items);
    foreach ($data['items'] as $key => $value) {
      if (isset($items_quantity[$key])) {
        $data['items'][$key]['qty'] = $items_quantity[$key];
      }

      // For the OOS.
      if ($data['in_stock'] && !$value['in_stock']) {
        $data['in_stock'] = FALSE;
      }

      // This is to determine whether item to be shown free or not in cart.
      $data['items'][$key]['free_item'] = FALSE;
      foreach ($cart_data['totals']['items'] as $total_item) {
        if (in_array($value['sku'], array_keys($items_id))
          && $items_id[$value['sku']] == $total_item['item_id']
          && ($total_item['price'] * $items_quantity[$value['sku']] === $total_item['discount_amount'])) {
          $data['items'][$key]['free_item'] = TRUE;
          break;
        }
      }
    }

    $data['cart_promo'] = [];
    // If there is any rule applied on cart.
    if (!empty($cart_data['cart']['applied_rule_ids'])) {
      $drupal_promos_data = $this->drupal->getAllPromoData();
      // If we have promo data from drupal.
      if (!empty($drupal_promos_data)) {
        $cart_promo_rule_ids = explode(',', $cart_data['cart']['applied_rule_ids']);
        foreach ($drupal_promos_data as $drupal_promo_data) {
          // If there is any rule applied on cart.
          if (in_array($drupal_promo_data['commerce_id'], $cart_promo_rule_ids)) {
            $data['cart_promo'][] = [
              'label' => $drupal_promo_data['promo_label'],
              'description' => strip_tags($drupal_promo_data['promo_desc']),
            ];
            // If rule is of type `free shipping`.
            if ($drupal_promo_data['promo_sub_tpe'] == 'free_shipping_order') {
              $data['totals']['free_delivery'] = TRUE;
            }
          }
        }
      }
    }

    // Prepare recommended product data.
    $recommended_products = $this->drupal->getDrupalLinkedSkus($sku_items);
    $recommended_products_data = [];
    $data['recommended_products'] = [];
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
        $cart = $this->cart->addUpdateRemoveItem($cart_id, $request_content['sku'], $request_content['quantity'], CartActions::CART_ADD_ITEM, $request_content['options']);

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
        $options = [];
        if ($action == CartActions::CART_ADD_ITEM) {
          $options = $request_content['options'];
        }
        $cart = $this->cart->addUpdateRemoveItem($cart_id, $request_content['sku'], $request_content['quantity'], $action, $options);

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
