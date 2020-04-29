<?php

namespace App\Controller;

use App\Service\CheckoutCom\APIWrapper;
use App\Service\Magento\CartActions;
use App\Service\Cart;
use App\Service\Drupal\Drupal;
use App\Service\Magento\MagentoCustomer;
use App\Service\Magento\MagentoInfo;
use App\Service\SessionStorage;
use App\Service\Utility;
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
   * Magento Customer service.
   *
   * @var \App\Service\Magento\MagentoCustomer
   */
  protected $magentoCustomer;

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
   * @var \App\Service\SessionStorage
   */
  protected $session;

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
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   * @param \App\Service\Magento\MagentoCustomer $magento_customer
   *   Magento Customer service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\SessionStorage $session
   *   Service for session.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(RequestStack $request,
                              Cart $cart,
                              Drupal $drupal,
                              MagentoInfo $magento_info,
                              MagentoCustomer $magento_customer,
                              LoggerInterface $logger,
                              SessionStorage $session,
                              Utility $utility) {
    $this->request = $request->getCurrentRequest();
    $this->cart = $cart;
    $this->drupal = $drupal;
    $this->magentoInfo = $magento_info;
    $this->magentoCustomer = $magento_customer;
    $this->logger = $logger;
    $this->session = $session;
    $this->utility = $utility;
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

  /**
   * Get cart data.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Cart response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getCart() {
    $cart_id = $this->session->getDataFromSession(Cart::SESSION_STORAGE_KEY);
    if (empty($cart_id)) {
      // In JS we will consider this as empty cart.
      return new JsonResponse(['error' => TRUE]);
    }

    $data = $this->cart->getCart();

    // Check customer email And check drupal session customer id to validate,
    // if current cart is associated with logged in user or not.
    if (empty($data['cart']['customer']['email']) && $customer_id = $this->getDrupalInfo('customer_id')) {
      $this->cart->associateCartToCustomer($customer_id);
      $data = $this->cart->getCart();
    }

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
    if (!empty($data['cart']['items']) && !empty($this->getDrupalInfo('uid'))) {
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
    if (empty($this->cart->getCartId())) {
      $info = $this->drupal->getSessionCustomerInfo();
      if (!empty($info['customer_id'])) {
        $cart_id = $this->cart->createCart($info['customer_id']);
        $this->session->updateDataInSession(Cart::SESSION_STORAGE_KEY, $cart_id);
      }
    }

    return $this->getCart();
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

    $data['appliedRules'] = $cart_data['cart']['applied_rule_ids'] ?? [];

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
    if (isset($cart_data['cart']['extension_attributes'], $cart_data['cart']['extension_attributes']['shipping_assignments'])
      && !empty($shipping_info = $cart_data['cart']['extension_attributes']['shipping_assignments'])
      && !empty($shipping_info = $cart_data['cart']['extension_attributes']['shipping_assignments'][0]['shipping'])) {
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

      $data['billing_address'] = NULL;
      // Assuming if billing address has first name set,
      // means billing address available.
      if (!empty($cart_data['cart']['billing_address'])
        && !empty($cart_data['cart']['billing_address']['firstname'])) {
        $data['billing_address'] = $cart_data['cart']['billing_address'];
        foreach ($data['billing_address']['custom_attributes'] as $key => $attribute) {
          $data['billing_address'][$attribute['attribute_code']] = $attribute['value'];
        }
        unset($data['billing_address']['custom_attributes']);
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

    $data['payment_methods'] = !empty($shipping_info['method'])
      ? $this->cart->getPaymentMethods($cart_data['cart']['id'])
      : [];

    // Payment method set on cart.
    // @Todo: This should be done actually in magento.
    $data['cart_payment_method'] = !empty($shipping_info['method'])
      ? $this->cart->getPaymentMethodSetOnCart($cart_data['cart']['id'])
      : NULL;

    $data['coupon_code'] = $cart_data['totals']['coupon_code'] ?? '';

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
    // Whether CnC enabled or not.
    $data['cnc_enabled'] = TRUE;

    $sku_items = array_column($cart_data['cart']['items'], 'sku');
    $items_quantity = array_column($cart_data['cart']['items'], 'qty', 'sku');
    $items_price = array_column($cart_data['cart']['items'], 'price', 'sku');
    $items_id = array_column($cart_data['cart']['items'], 'item_id', 'sku');
    $items_name = array_column($cart_data['cart']['items'], 'name', 'sku');
    $extension_attributes = array_column($cart_data['cart']['items'], 'extension_attributes', 'sku');
    try {
      $data['items'] = [];
      foreach ($sku_items as $sku) {
        $data['items'][$sku]['title'] = $items_name[$sku];
        $data['items'][$sku]['qty'] = $items_quantity[$sku];
        $data['items'][$sku]['price'] = $items_price[$sku];
        $data['items'][$sku]['sku'] = $sku;
        $data['items'][$sku]['id'] = $items_id[$sku];

        if (isset($extension_attributes[$sku]) && $extension_attributes[$sku]['error_message']
          && $extension_attributes[$key]['error_message']) {
          $data['items'][$sku]['error_msg'] = $extension_attributes[$sku]['error_message'];
          $data['is_error'] = TRUE;
        }

        // This is to determine whether item to be shown free or not in cart.
        $data['items'][$sku]['freeItem'] = FALSE;
        foreach ($cart_data['totals']['items'] as $total_item) {
          // If total price of item matches discount, we mark as free.
          if ($items_id[$sku] == $total_item['item_id']
            && $total_item['price'] * $items_quantity[$sku] === $total_item['discount_amount']) {
            $data['items'][$sku]['freeItem'] = TRUE;
            break;
          }
        }

        // Get stock data.
        $stockInfo = $this->drupal->getCartItemDrupalStock($sku);
        $data['items'][$sku]['in_stock'] = $stockInfo['in_stock'];
        $data['items'][$sku]['stock'] = $stockInfo['stock'];
        $data['items'][$sku]['max_sale_qty'] = $stockInfo['max_sale_qty'] ?? 0;

        // If info is available in static array, means this we get from
        // the cart update operation. We use that.
        if (!empty(Cart::$stockInfo)
          && isset(Cart::$stockInfo[$sku])
          && !Cart::$stockInfo[$sku]) {
          $data['items'][$sku]['in_stock'] = FALSE;
          $data['items'][$sku]['stock'] = 0;
        }

        // If any item is OOS.
        if (!$data['items'][$sku]['in_stock'] || $data['items'][$sku]['stock'] == 0) {
          $data['in_stock'] = FALSE;
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

      $data['uid'] = $this->getDrupalInfo('uid') ?: 0;
    }
    catch (\Exception $e) {
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }

    return $data;
  }

  /**
   * Cart controller for cart update operations.
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

    // Validate request.
    if (!$this->validateRequestData($request_content)) {
      // Return error response if not valid data.
      // Setting custom error code for bad response so that
      // we could distinguish this error.
      return new JsonResponse($this->utility->getErrorResponse($this->utility->getDefaultErrorMessage(), '400'));
    }

    $action = $request_content['action'];

    switch ($action) {
      case CartActions::CART_ADD_ITEM:
      case CartActions::CART_UPDATE_ITEM:
      case CartActions::CART_REMOVE_ITEM:
        $options = [];
        if ($action == CartActions::CART_ADD_ITEM) {
          // If we try to add item while we don't have anything or corrupt
          // session, we create cart object.
          if (empty($this->cart->getCartId())) {
            $cart_id = $this->cart->createCart();
            // Pass exception to response.
            if (is_array($cart_id)) {
              return new JsonResponse($cart_id);
            }

            // Associate cart to customer.
            $customer_id = $this->getDrupalInfo('customer_id');
            if ($customer_id > 0) {
              $this->cart->associateCartToCustomer($customer_id);
            }

          }
          $options = $request_content['options'];
        }
        $cart = $this->cart->addUpdateRemoveItem($request_content['sku'], $request_content['quantity'], $action, $options);
        break;

      case CartActions::CART_APPLY_COUPON:
      case CartActions::CART_REMOVE_COUPON:
        $cart = $this->cart->applyRemovePromo($request_content['promo'], $action);
        break;

      case CartActions::CART_SHIPPING_UPDATE:
        $shipping_info = $request_content['shipping_info'];
        $update_billing = $request_content['update_billing'];

        $email = $shipping_info['static']['email'];

        // Cart customer validations.
        $uid = (int) $this->getDrupalInfo('uid');
        $cart_customer_id = $this->cart->getCartCustomerId();
        if (empty($uid) && (empty($cart_customer_id) || ($this->cart->getCartCustomerEmail() !== $email))) {
          $customer = $this->magentoCustomer->getCustomerByMail($email);
          if (empty($customer)) {
            $customer = $this->magentoCustomer->createCustomer(
              $email,
              $shipping_info['static']['firstname'],
              $shipping_info['static']['lastname']
            );
          }

          if ($customer && $customer['id']) {
            $result = $this->cart->associateCartToCustomer($customer['id']);
            if (is_array($result) && !empty($result['error'])) {
              return new JsonResponse($result);
            }
          }
        }

        if ($shipping_info['shipping_type'] == 'cnc') {
          // Unset as not needed in further processing.
          unset($shipping_info['shipping_type']);
          $cart = $this->cart->addCncShippingInfo($shipping_info, $action, $update_billing);
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
            $shipping_methods = $this->cart->shippingMethods($shipping_data);

            // If no shipping method.
            if (empty($shipping_methods)) {
              return new JsonResponse(['error' => TRUE]);
            }
          }

          $shipping_info['carrier_info'] = [
            'code' => $shipping_methods[0]['carrier_code'],
            'method' => $shipping_methods[0]['method_code'],
          ];

          $cart = $this->cart->addShippingInfo($shipping_info, $action, $update_billing);
        }
        break;

      case CartActions::CART_BILLING_UPDATE:
        $billing_info = $request_content['billing_info'];
        $billing_data = $this->cart->formatAddressForShippingBilling($billing_info);
        $cart = $this->cart->updateBilling($billing_data);
        break;

      case CartActions::CART_PAYMENT_FINALISE:
        $extension = [
          'attempted_payment' => 1,
        ];

        try {
          $request_content['payment_info']['payment']['additional_data'] = $this->cart->processPaymentData(
            $request_content['payment_info']['payment']['method'],
            $request_content['payment_info']['payment']['additional_data']
          );
        }
        catch (\Exception $e) {
          if ($e->getCode() === 302) {
            // Set attempted payment 1 before redirecting.
            $this->cart->updatePayment($request_content['payment_info']['payment'], $extension);

            return new JsonResponse([
              'success' => TRUE,
              'redirectUrl' => $e->getMessage(),
            ]);
          }
          elseif ($e->getCode() === 400) {
            // Cancel reservation api when process failed for not enough data,
            // or bad data. i.e. checkout.com cvv missing.
            $this->cart->cancelCartReservation($e->getMessage());
            return new JsonResponse([
              'error' => TRUE,
              'message' => $e->getMessage(),
            ]);
          }
          else {
            $this->cart->cancelCartReservation($e->getMessage());
            return new JsonResponse([
              'error' => TRUE,
              'message' => $e->getMessage(),
            ]);
          }
        }

        if (!empty($request_content['payment_info']['payment']['additional_data']['public_hash'])) {
          $request_content['payment_info']['payment']['method'] = APIWrapper::CHECKOUT_COM_VAULT_METHOD;
        }

        $cart = $this->cart->updatePayment($request_content['payment_info']['payment'], $extension);
        break;

      case CartActions::CART_PAYMENT_UPDATE:
        $extension = [];

        if (isset($request_content['payment_info']['payment']['analytics'])) {
          $extension['ga_client_id'] = $request_content['payment_info']['payment']['analytics']['clientId'];
          $extension['tracking_id'] = $request_content['payment_info']['payment']['analytics']['trackingId'];
          $extension['user_id'] = $this->cart->getCartCustomerId();
          $extension['user_type'] = $this->getDrupalInfo('uid') > 0 ? 'Logged in User' : 'Guest User';
          $extension['user_agent'] = $this->request->headers->get('User-Agent', '');
          $extension['client_ip'] = $_ENV['AH_CLIENT_IP'] ?? $this->request->getClientIp();
        }

        $cart = $this->cart->updatePayment($request_content['payment_info']['payment'], $extension);
        break;

      case CartActions::CART_REFRESH:
        // If cart id in request not matches with what in session.
        if ($request_content['cart_id'] !== $this->cart->getCartId()) {
          // Return error response if not valid data.
          return new JsonResponse($this->utility->getErrorResponse('Invalid cart', '500'));
        }

        $postData = $request_content['postData'];
        $cart = $this->cart->updateCart($postData);
        break;
    }

    if (empty($cart) || !empty($cart['error'])) {
      return new JsonResponse($cart ?? []);
    }

    // Here we will do the processing of cart to make it in required format.
    $cart = $this->getProcessedCartData($cart);
    return new JsonResponse($cart);
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
    if (!isset($request_content['data'])) {
      return new JsonResponse($this->utility->getErrorResponse('Invalid request', '500'));
    }

    $result = $this->cart->placeOrder($request_content['data']);

    if (!isset($result['error'])) {
      $response = [
        'success' => TRUE,
        'redirectUrl' => 'checkout/confirmation?id=' . $result['secure_order_id'],
      ];

      return new JsonResponse($response);
    }

    return new JsonResponse($result);
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
    // If action info or cart id not available.
    if (empty($request_content['action'])) {
      return FALSE;
    }

    // For new cart request, we don't need any further validations.
    if ($request_content['action'] === CartActions::CART_ADD_ITEM
      && empty($request_content['cart_id'])) {
      return TRUE;
    }

    // Backend validation.
    $uid = (int) $this->getDrupalInfo('uid');
    $session_customer_id = $this->getDrupalInfo('customer_id');
    $cart_customer_id = $this->cart->getCartCustomerId();
    if ($uid > 0) {
      if (empty($cart_customer_id)) {
        // @TODO: Check if we should associate cart and proceed.
        return FALSE;
      }

      // This is serious.
      if ($cart_customer_id !== $session_customer_id) {
        return FALSE;
      }
    }

    return TRUE;
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
    try {
      if (empty($this->cart->getCartId())) {
        return new JsonResponse($this->utility->getErrorResponse('No cart in session', 404));
      }

      $customer = $this->drupal->getSessionCustomerInfo();

      if (empty($customer)) {
        return new JsonResponse($this->utility->getErrorResponse('No user in session', 404));
      }

      // Check if association is not required.
      if ($customer['customer_id'] === $this->cart->getCartCustomerId()) {
        return $this->getCart();
      }

      $this->cart->associateCartToCustomer($customer['customer_id']);
    }
    catch (\Exception $e) {
      // Exception handling here.
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }

    return $this->getCart();
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
          if (empty($shipping_methods) || !empty($shipping_methods['error'])) {
            return $data;
          }

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
          $data = $this->cart->addShippingInfo($shipping_data, CartActions::CART_SHIPPING_UPDATE);
          break;
        }
      }
    }

    return $data;
  }

}
