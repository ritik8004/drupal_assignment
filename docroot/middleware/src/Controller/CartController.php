<?php

namespace App\Controller;

use App\Service\Magento\CartActions;
use App\Service\Cart;
use App\Service\Drupal\Drupal;
use App\Service\Magento\MagentoInfo;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class CartController.
 */
class CartController {

  /**
   * The cart storage key.
   */
  const STORAGE_KEY = 'acq_cart_middleware';

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
   * Service for session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Utility.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * Current cart session info.
   *
   * @var array
   */
  protected $sessionCartInfo = [];

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
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   Service for session.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(RequestStack $request,
                              Cart $cart,
                              Drupal $drupal,
                              MagentoInfo $magento_info,
                              LoggerInterface $logger,
                              SessionInterface $session,
                              Utility $utility) {
    $this->request = $request->getCurrentRequest();
    $this->cart = $cart;
    $this->drupal = $drupal;
    $this->magentoInfo = $magento_info;
    $this->logger = $logger;
    $this->session = $session;
    $this->utility = $utility;
  }

  /**
   * Start session and set cart data.
   *
   * @param bool $force
   *   TRUE to load cart info from session forcefully, false otherwise.
   */
  protected function loadCartFromSession($force = FALSE) {
    if (!$this->session->isStarted()) {
      $this->session->start();
    }

    if (empty($this->sessionCartInfo) || $force) {
      $this->sessionCartInfo = $this->session->get(self::STORAGE_KEY);
    }
  }

  /**
   * Update cart id to middleware session.
   *
   * @param int $cart_id
   *   The cart id.
   * @param int|null $customer_id
   *   (Optional) the customer id.
   * @param string|null $email
   *   (Optional) the customer email.
   */
  protected function updateCartSession(int $cart_id, int $customer_id = NULL, $email = NULL) {
    $this->loadCartFromSession();
    $update = FALSE;
    if (empty($this->sessionCartInfo['cart_id'])) {
      $update = TRUE;
      $this->sessionCartInfo['cart_id'] = $cart_id;
    }

    if (!empty($customer_id)) {
      $update = TRUE;
      $this->sessionCartInfo['customer_id'] = (int) $customer_id;
    }

    if (!empty($email)) {
      $update = TRUE;
      $this->sessionCartInfo['customer_email'] = $email;
    }

    if ($update) {
      $this->session->set(self::STORAGE_KEY, $this->sessionCartInfo);
    }
  }

  /**
   * Return user id from current session.
   *
   * @return int|null
   *   Return user id or null.
   */
  protected function getSessionUid() {
    if (!empty($this->sessionCartInfo['uid'])) {
      return $this->sessionCartInfo['uid'];
    }

    $this->loadCartFromSession();
    return !empty($this->sessionCartInfo['uid'])
      ? $this->sessionCartInfo['uid']
      : NULL;
  }

  /**
   * Return customer id from current session.
   *
   * @return int|null
   *   Return customer id or null.
   */
  protected function getSessionCustomerId() {
    if (!empty($this->sessionCartInfo['customer_id'])) {
      return $this->sessionCartInfo['customer_id'];
    }

    $this->loadCartFromSession();
    return !empty($this->sessionCartInfo['customer_id'])
      ? $this->sessionCartInfo['customer_id']
      : NULL;
  }

  /**
   * Get cart data.
   *
   * @param int $cart_id
   *   Cart id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Cart response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
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

    // If logged in user.
    if (!empty($this->getSessionUid())) {
      $shipping = $data['cart']['extension_attributes']['shipping_assignments'][0]['shipping'];
      // If shipping method is set and only HD.
      if ((empty($shipping['extension_attributes'])
        || $shipping['extension_attributes']['click_and_collect_type'] == 'home_delivery')
        && empty($shipping['method'])) {
        $data = $this->checkAndUpdateShippinginCart($data);
      }
    }

    // Here we will do the processing of cart to make it in required format.
    $data = $this->getProcessedCartData($data);
    return new JsonResponse($data);
  }

  /**
   * Restore cart.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Cart response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function restoreCart() {
    $this->loadCartFromSession();

    if (empty($this->sessionCartInfo['cart_id'])) {
      $this->sessionCartInfo = $this->drupal->getCustomerCart();
      if (!empty($this->sessionCartInfo)) {
        $this->session->set(self::STORAGE_KEY, $this->sessionCartInfo);
      }
    }

    if (!empty($this->sessionCartInfo['cart_id'])) {
      return $this->getCart($this->sessionCartInfo['cart_id']);
    }

    // If there are not cart available.
    $data = $this->utility->getErrorResponse('could not find any cart', 404);
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
    $data['customer'] = $cart_data['cart']['customer'] ?? NULL;
    $data['items_qty'] = $cart_data['cart']['items_qty'];
    $data['cart_total'] = $cart_data['totals']['base_grand_total'];
    $data['surcharge'] = $cart_data['cart']['extension_attributes']['surcharge'] ?? [];
    $data['totals'] = [
      'subtotal_incl_tax' => $cart_data['totals']['subtotal_incl_tax'],
      'shipping_incl_tax' => $cart_data['totals']['shipping_incl_tax'] ?? 0,
      'base_grand_total' => $cart_data['totals']['base_grand_total'],
      'discount_amount' => $cart_data['totals']['discount_amount'],
      'free_delivery' => FALSE,
      'surcharge' => 0,
    ];

    if (is_array($data['surcharge']) && $data['surcharge']['amount'] > 0 && $data['surcharge']['is_applied']) {
      $data['totals']['surcharge'] = $data['surcharge']['amount'];
    }

    // Store delivery method when required.
    if (!empty($shipping_info = $cart_data['cart']['extension_attributes']['shipping_assignments'][0]['shipping'])) {
      $data['carrier_info'] = $shipping_info['method'];

      if (empty($shipping_info['method']) && !empty($shipping_info['extension_attributes']['click_and_collect_type'])) {
        $shipping_info['method'] = 'click_and_collect_click_and_collect';
      }

      $data['shipping_address'] = NULL;
      if (!empty($shipping_info['method'])) {
        $custom_shipping_attributes = [];
        $data['shipping_address'] = $shipping_info['address'];
        foreach ($shipping_info['address']['custom_attributes'] as $key => $value) {
          $custom_shipping_attributes[$value['attribute_code']] = $value['value'];
        }
        unset($data['shipping_address']['custom_attributes']);
        $data['shipping_address'] += $custom_shipping_attributes;
      }

      $data['delivery_type'] = 'hd';
      if (!empty($shipping_info['extension_attributes']['click_and_collect_type'])) {
        $data['delivery_type'] = $shipping_info['extension_attributes']['click_and_collect_type'] == 'home_delivery' ? 'hd' : 'cnc';
        if ($data['delivery_type'] == 'cnc' && !empty($shipping_info['extension_attributes']['store_code'])) {
          $data['store_code'] = $shipping_info['extension_attributes']['store_code'];
          $data['store_info'] = $this->drupal->getStoreInfo($data['store_code']);
        }
      }

      if ($data['delivery_type'] == 'hd') {
        $data['shipping_methods'] = $this->cart->shippingMethods(['address' => $shipping_info['address']], $data['cart_id']);

        // Remove CnC from the methods.
        // @TODO: Get CnC method name from Drupal or from some config.
        $data['shipping_methods'] = array_filter($data['shipping_methods'], function ($method) {
          return ($method['carrier_code'] !== 'click_and_collect');
        });
      }
    }

    if (!empty($shipping_info['method'])) {
      $data['payment_methods'] = $this->cart->getPaymentMethods($cart_data['cart']['id']);
    }

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

    // Whether CnC enabled or not.
    $data['cnc_enabled'] = TRUE;

    $sku_items = array_column($cart_data['cart']['items'], 'sku');
    $items_quantity = array_column($cart_data['cart']['items'], 'qty', 'sku');
    $items_id = array_column($cart_data['cart']['items'], 'item_id', 'sku');
    try {
      $data['items'] = $this->drupal->getCartItemDrupalData($sku_items);
      foreach ($data['items'] as $key => $value) {
        if (isset($items_quantity[$key])) {
          $data['items'][$key]['qty'] = $items_quantity[$key];
        }

        // If CnC is disabled for any item, we don't process and consider
        // CnC disabled.
        if ($data['cnc_enabled'] && isset($data['items'][$key]['delivery_options']['click_and_collect'])) {
          $data['cnc_enabled'] = $data['items'][$key]['delivery_options']['click_and_collect']['status'];
        }

        // For the OOS.
        if ($data['in_stock'] && (isset($value['in_stock']) && !$value['in_stock'])) {
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

      $this->updateCartSession($data['cart_id'], $data['customer']['id'], $data['customer']['email']);
      $data['uid'] = $this->getSessionUid() ?: 0;
    }
    catch (\Exception $e) {
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }

    return $data;
  }

  /**
   * Retrieve cart from session on trying to create a new cart.
   *
   * @return int
   *   Return cart id from session if cart data exists else create new cart id.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function createCart() {
    $this->loadCartFromSession();
    if (!empty($this->sessionCartInfo['cart_id'])) {
      // Validate the cart again to ensure session data is not corrupt.
      $data = $this->cart->getCart($this->sessionCartInfo['cart_id']);
      if (empty($data['error'])) {
        return $this->sessionCartInfo['cart_id'];
      }
    }

    $this->session->remove(self::STORAGE_KEY);
    $this->sessionCartInfo = [];
    return $this->cart->createCart();
  }

  /**
   * Update cart controller.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function updateCart(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);

    $this->loadCartFromSession();

    // Validate request.
    if (!$this->validateRequestData($request_content)) {
      // Return error response if not valid data.
      return new JsonResponse($this->utility->getErrorResponse('Invalid data', '500'));
    }

    $action = $request_content['action'];

    switch ($action) {
      case CartActions::CART_CREATE_NEW:
        // Get cart id from session or create a new cart id.
        $cart_id = $this->createCart();

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

      case CartActions::CART_SHIPPING_UPDATE:
        $cart_id = $request_content['cart_id'];
        $shipping_info = $request_content['shipping_info'];

        if ($shipping_info['shipping_type'] == 'cnc') {
          // Unset as not needed in further processing.
          unset($shipping_info['shipping_type']);

          // Create customer if the condition resolves to true.
          $create_customer = !(
            $this->sessionCartInfo['cart_id'] == $cart_id
            && !empty($this->sessionCartInfo['customer_id'])
          ) || ($this->sessionCartInfo['customer_email'] != $shipping_info['static']['email']);
          $cart = $this->cart->addCncShippingInfo($cart_id, $shipping_info, $action, $create_customer);
        }
        else {
          $shipping_methods = [];
          $carrier_info = [];
          if (!empty($shipping_info['carrier_info'])) {
            $carrier_info = $shipping_info['carrier_info'];
            unset($shipping_info['carrier_info']);
          }

          $shipping_data = $this->cart->prepareShippingData($shipping_info);

          // If carrier info available in request, use that
          // instead getting shipping methods.
          if (!empty($carrier_info)) {
            $shipping_methods[] = [
              'carrier_code' => $carrier_info['carrier'],
              'method_code' => $carrier_info['method'],
            ];
          }
          else {
            $shipping_methods = $this->cart->shippingMethods($shipping_data, $cart_id);

            // If no shipping method.
            if (empty($shipping_methods)) {
              return new JsonResponse(['error' => TRUE]);
            }
          }

          // If we update/add shipping in cart by address id.
          if (!empty($shipping_info['address_id'])) {
            $shipping_info = [
              'customer_address_id' => $shipping_info['address_id'],
              'address' => [
                'customer_address_id' => $shipping_info['address_id'],
                'country_id' => $shipping_info['country_id'],
                'customer_id' => $this->getSessionCustomerId(),
              ],
              'carrier_info' => [
                'code' => $shipping_methods[0]['carrier_code'],
                'method' => $shipping_methods[0]['method_code'],
              ],
            ];
          }
          else {
            $shipping_info['carrier_info'] = [
              'code' => $shipping_methods[0]['carrier_code'],
              'method' => $shipping_methods[0]['method_code'],
            ];
          }

          $cart = $this->cart->addShippingInfo($cart_id, $shipping_info, $action);
        }

        if (!empty($cart['error'])) {
          return new JsonResponse($cart);
        }

        // Here we will do the processing of cart to make it in required format.
        $cart = $this->getProcessedCartData($cart);
        return new JsonResponse($cart);

      case CartActions::CART_PAYMENT_UPDATE:
        $cart_id = $request_content['cart_id'];
        $cart = $this->cart->updatePayment($cart_id, $request_content['payment_info'], $action);

        if (!empty($cart['error'])) {
          return new JsonResponse($cart);
        }

        // Here we will do the processing of cart to make it in required format.
        $cart = $this->getProcessedCartData($cart);
        return new JsonResponse($cart);
    }
  }

  /**
   * Gets shipping methods.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function shippingMethods(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);
    if (!isset($request_content['cart_id'], $request_content['data'])) {
      return new JsonResponse($this->utility->getErrorResponse('Invalid request', '500'));
    }

    $data = $this->cart->prepareShippingData($request_content['data']);

    $methods = $this->cart->shippingMethods($data, $request_content['cart_id']);
    return new JsonResponse($methods);
  }

  /**
   * Place order.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function placeOrder(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);
    if (!isset($request_content['cart_id'], $request_content['data'])) {
      return new JsonResponse($this->utility->getErrorResponse('Invalid request', '500'));
    }

    $result = $this->cart->placeOrder($request_content['cart_id'], $request_content['data']);
    if (is_string($result)) {
      $this->session->remove(self::STORAGE_KEY);
      $last_order = str_replace('"', '', $result);
      $this->session->set(OrdersController::SESSION_STORAGE_KEY, (int) $last_order);
    }

    return new JsonResponse($result);
  }

  /**
   * Check if a customer by given email exists or not.
   *
   * @param string $email
   *   Email address.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Payment method response.
   */
  public function customerCheckByMail(string $email) {
    $domain = explode('@', $email)[1];
    $dns_records = dns_get_record($domain);
    if (empty($dns_records)) {
      return new JsonResponse([
        'exists' => 'wrong',
        'email' => $email,
      ]);
    }

    $customer = $this->cart->customerCheckByMail($email);
    $customer_exists = ($customer['total_count'] > 0);
    return new JsonResponse(['exists' => $customer_exists]);
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

  /**
   * Associate cart with active user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function associateCart() {
    $this->loadCartFromSession(TRUE);

    try {
      if (empty($this->sessionCartInfo['cart_id'])) {
        return $this->utility->getErrorResponse('No cart in session', 404);
      }

      $customer = $this->drupal->getSessionCustomerInfo();

      if (empty($customer)) {
        return $this->utility->getErrorResponse('No user in session', 404);
      }

      // Check if association is not required.
      if ($customer['customer_id'] === $this->sessionCartInfo['customer_id']) {
        return $this->getCart($this->sessionCartInfo['cart_id']);
      }

      $this->cart->associateCartToCustomer($this->sessionCartInfo['cart_id'], $customer['customer_id']);
      $this->session->set(self::STORAGE_KEY, [
        'cart_id' => $this->sessionCartInfo['cart_id'],
        'customer_id' => $customer['customer_id'],
        'uid' => $customer['uid'],
      ]);

      $this->loadCartFromSession(TRUE);
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }

    return $this->getCart($this->sessionCartInfo['cart_id']);
  }

  /**
   * Adds shipping info for logged in user.
   *
   * @param array $data
   *   Cart data.
   *
   * @return array
   *   Updated cart data.
   */
  public function checkAndUpdateShippinginCart(array $data) {
    // If Customer address available for cart.
    if (!empty($data['cart']['customer']['addresses'])) {
      foreach ($data['cart']['customer']['addresses'] as $address) {
        // If address is set as default for shipping.
        if (!empty($address['default_shipping']) && $address['default_shipping']) {
          // Region key should be string.
          if (!empty($address['region']) && is_array($address['region'])) {
            unset($address['region']);
          }
          $shipping_methods = $this->cart->shippingMethods(['address' => $address], $data['cart']['id']);
          $shipping_data = [
            'customer_address_id' => $address['id'],
            'address' => [
              'customer_address_id' => $address['id'],
              'country_id' => $address['country_id'],
              'customer_id' => $address['customer_id'],
            ],
            'carrier_info' => [
              'code' => $shipping_methods[0]['carrier_code'],
              'method' => $shipping_methods[0]['method_code'],
            ],
          ];
          $data = $this->cart->addShippingInfo($data['cart']['id'], $shipping_data, CartActions::CART_SHIPPING_UPDATE);
          break;
        }
      }
    }

    return $data;
  }

}
