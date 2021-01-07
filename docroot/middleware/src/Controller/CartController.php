<?php

namespace App\Controller;

use App\Helper\CustomerHelper;
use App\Service\CartErrorCodes;
use App\Service\CheckoutCom\APIWrapper;
use App\Service\CheckoutDefaults;
use App\Service\Config\SystemSettings;
use App\Service\Magento\CartActions;
use App\Service\Cart;
use App\Service\Drupal\Drupal;
use App\Service\Magento\MagentoCustomer;
use App\Service\Magento\MagentoInfo;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Cart Controller.
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
   * Service to check and apply defaults on Cart.
   *
   * @var \App\Service\CheckoutDefaults
   */
  protected $checkoutDefaults;

  /**
   * Utility.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

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
   * @param \App\Service\CheckoutDefaults $checkout_defaults
   *   Service to check and apply defaults on Cart.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   */
  public function __construct(RequestStack $request,
                              Cart $cart,
                              Drupal $drupal,
                              MagentoInfo $magento_info,
                              MagentoCustomer $magento_customer,
                              LoggerInterface $logger,
                              CheckoutDefaults $checkout_defaults,
                              Utility $utility,
                              SystemSettings $settings) {
    $this->request = $request->getCurrentRequest();
    $this->cart = $cart;
    $this->drupal = $drupal;
    $this->magentoInfo = $magento_info;
    $this->magentoCustomer = $magento_customer;
    $this->logger = $logger;
    $this->checkoutDefaults = $checkout_defaults;
    $this->utility = $utility;
    $this->settings = $settings;
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
    $cart_id = $this->cart->getCartId();
    if (empty($cart_id)) {
      // In JS we will consider this as empty cart.
      return new JsonResponse(['error' => TRUE]);
    }

    $data = $this->cart->getRestoredCart();

    // Check customer email And check drupal session customer id to validate,
    // if current cart is associated with logged in user or not.
    if (empty($data['customer']['email']) && $customer_id = $this->cart->getDrupalInfo('customer_id')) {
      $this->cart->associateCartToCustomer($customer_id);
      $data = $this->cart->getCart();
    }

    if (empty($data)) {
      $this->logger->error('Cart is no longer available.');
      return new JsonResponse(['error' => TRUE]);
    }

    // If there is any exception/error, return as is with exception message
    // without processing further.
    if (!empty($data['error'])) {
      $this->logger->error('Error while getting cart:@cart_id Error:@error', [
        '@cart_id' => $cart_id,
        '@error' => json_encode($data),
      ]);

      return new JsonResponse($data);
    }

    // Here we will do the processing of cart to make it in required format.
    $data = $this->cart->getProcessedCartData($data);
    return new JsonResponse($data);
  }

  /**
   * Get cart data for checkout.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Cart response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getCartForCheckout() {
    $cart_id = $this->cart->getCartId();
    if (empty($cart_id)) {
      // In JS we will consider this as empty cart.
      return new JsonResponse(['error' => TRUE]);
    }

    // Always get fresh cart for checkout page.
    $data = $this->cart->getCart(TRUE);

    // Check customer email And check drupal session customer id to validate,
    // if current cart is associated with logged in user or not.
    $sessionCustomerId = $this->cart->getDrupalInfo('customer_id');
    if ($sessionCustomerId && (empty($data['customer']['id']) || $data['customer']['id'] != $sessionCustomerId)) {
      $this->cart->associateCartToCustomer($sessionCustomerId, TRUE);
      $data = $this->cart->getCart();
    }

    // If there is any exception/error, return as is with exception message
    // without processing further.
    if (empty($data) || !empty($data['error'])) {
      $this->logger->error('Error while getting cart:@cart_id Error:@error', [
        '@cart_id' => $cart_id,
        '@error' => json_encode($data),
      ]);

      return new JsonResponse($data);
    }

    if (empty($data['cart']['items'])) {
      $this->logger->error('Checkout accessed without items in cart for id @cart_id', [
        '@cart_id' => $cart_id,
      ]);

      return new JsonResponse($this->utility->getErrorResponse('Checkout accessed without items in cart', 500));
    }

    $response = $this->getProcessedCheckoutData($data);

    return new JsonResponse($response);
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
        $cart_id = $this->cart->searchCart($info['customer_id']);
        if ($cart_id > 0) {
          $this->cart->setCartId($cart_id);
        }
      }
      else {
        // @todo Remove this "else" part and getAcmCartId() when we
        // uninstall alshaya_acm module.
        $info = $this->drupal->getAcmCartId();
        // Set the cart_id in current session, if Drupal api returns the
        // cart_id. If the cart_id is not valid, or contains any error getCart()
        // will set the session key to NULL.
        if ($info['cart_id']) {
          $this->cart->setCartId($info['cart_id']);
        }
      }
    }

    return $this->getCart();
  }

  /**
   * Process cart data for checkout.
   *
   * @param array $data
   *   Cart data.
   *
   * @return array
   *   Processed data.
   */
  private function getProcessedCheckoutData(array $data) {
    if (isset($data['error'])) {
      return $data;
    }

    // Whether CnC enabled or not.
    $cnc_status = $this->cart->getCncStatusForCart($data);

    // Here we will do the processing of cart to make it in required format.
    $uid = $this->cart->getDrupalInfo('uid') ?: 0;

    if ($updated = $this->checkoutDefaults->applyDefaults($data, $uid)) {
      $data = $updated;
    }

    if (empty($data['shipping']['methods'])
        && !empty($data['shipping']['address'])
        && $data["shipping"]["type"] !== 'click_and_collect'
    ) {
      $shipping_methods = $this->cart->getHomeDeliveryShippingMethods($data['shipping']);

      if (isset($shipping_methods['error'])) {
        return $shipping_methods;
      }

      $data['shipping']['methods'] = $shipping_methods;
    }

    if (empty($data['payment']['methods']) && !empty($data['shipping']['method'])) {
      $data['payment']['methods'] = $this->cart->getPaymentMethods();
      $data['payment']['method'] = $this->cart->getPaymentMethodSetOnCart();
    }

    // Re-use the processing done for cart page.
    $response = $this->cart->getProcessedCartData($data);

    $response['cnc_enabled'] = $cnc_status;

    $response['customer'] = CustomerHelper::getCustomerPublicData($data['customer'] ?? []);
    $response['shipping'] = $data['shipping'] ?? [];

    if (!empty($response['shipping']['storeCode'])) {
      $response['shipping']['storeInfo'] = $this->drupal->getStoreInfo($response['shipping']['storeCode']);
      // Set the CnC type (rnc or sts) if not already set.
      if (empty($response['shipping']['storeInfo']['rnc_available'])
       && !empty($response['shipping']['clickCollectType'])) {
        $response['shipping']['storeInfo']['rnc_available'] = ($response['shipping']['clickCollectType'] == 'reserve_and_collect');
      }
    }
    $response['payment'] = $data['payment'] ?? [];

    // Set method to null if empty to reduce the number of conditions in JS.
    $response['shipping']['method'] = !empty($response['shipping']['method'])
      ? $response['shipping']['method']
      : NULL;

    // Format addresses.
    $response['shipping']['address'] = CustomerHelper::formatAddressForFrontend($response['shipping']['address'] ?? []);
    $response['billing_address'] = CustomerHelper::formatAddressForFrontend($data['cart']['billing_address'] ?? []);

    // If payment method is not available in the list, we set the first
    // available payment method.
    if (!empty($response['payment'])) {
      $codes = array_column($response['payment']['methods'], 'code');
      if (!empty($response['payment']['method'])
        && !in_array($response['payment']['method'], $codes)) {
        unset($response['payment']['method']);
      }

      // If default also has invalid payment method, we remove it
      // so that first available payment method will be selected.
      if (!empty($response['payment']['default'])
        && !in_array($response['payment']['default'], $codes)) {
        unset($response['payment']['default']);
      }

      if (!empty($response['payment']['method'])) {
        $response['payment']['method'] = $this->cart->getMethodCodeForFrontend($response['payment']['method']);
      }

      if (!empty($response['payment']['default'])) {
        $response['payment']['default'] = $this->cart->getMethodCodeForFrontend($response['payment']['default']);
      }
    }

    return $response;
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
    if (empty($request_content)) {
      // Return error response if not valid data.
      // Setting custom error code for bad response so that
      // we could distinguish this error.
      return new JsonResponse($this->utility->getErrorResponse($this->utility->getDefaultErrorMessage(), '500'));
    }

    $validation_response = $this->validateRequestData($request_content);
    if ($validation_response !== 200) {
      return new JsonResponse($this->utility->getErrorResponse($this->utility->getDefaultErrorMessage(), $validation_response));
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
            $customer_id = $this->cart->getDrupalInfo('customer_id');
            if ($customer_id > 0) {
              $this->cart->associateCartToCustomer($customer_id);
            }

          }
          $options = $request_content['options'];
        }
        elseif ($action == CartActions::CART_REMOVE_ITEM) {
          // If it is free gift with coupon, remove coupon too.
          $item = $this->cart->getCartItem($request_content['sku']);

          if (empty($item['price'])
            && !empty($item['extension_attributes'])
            && !empty($item['extension_attributes']['promo_rule_id'])
            && !empty($this->cart->getCoupon())) {
            $this->cart->applyRemovePromo('', CartActions::CART_REMOVE_COUPON);
          }
        }

        $cart = $this->cart->addUpdateRemoveItem($request_content['sku'], $request_content['quantity'], $action, $options, ($request_content['variant_sku'] ?? NULL));
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
        $uid = (int) $this->cart->getDrupalInfo('uid');
        $cart_customer_id = $this->cart->getCartCustomerId();
        if (empty($uid) && (empty($cart_customer_id) || ($this->cart->getCartCustomerEmail() !== $email))) {
          $customer = $this->magentoCustomer->getCustomerByMail($email);

          if (isset($customer['error'])) {
            return new JsonResponse($customer);
          }

          if (empty($customer)) {
            $customer = $this->magentoCustomer->createCustomer(
              $email,
              $shipping_info['static']['firstname'],
              $shipping_info['static']['lastname']
            );

            if (isset($customer['error'])) {
              return new JsonResponse($customer);
            }
          }

          if ($customer && $customer['id']) {
            $result = $this->cart->associateCartToCustomer($customer['id']);
            if (is_array($result) && !empty($result['error'])) {
              return new JsonResponse($result);
            }
          }
        }

        $type = $shipping_info['shipping_type'] ?? 'home_delivery';
        if ($type === 'click_and_collect') {
          // Unset as not needed in further processing.
          unset($shipping_info['shipping_type']);
          $this->logger->notice('Shipping update manual for CNC. Data: @data Address: @address Cart: @cart_id.', [
            '@address' => json_encode($shipping_info),
            '@data' => json_encode($request_content),
            '@cart_id' => $this->cart->getCartId(),
          ]);
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
          $hd_shipping_methods = [];
          if (!empty($carrier_info)) {
            $shipping_methods[] = [
              'carrier_code' => $carrier_info['carrier'],
              'method_code' => $carrier_info['method'],
            ];
          }
          else {
            $shipping_methods = $this->cart->getHomeDeliveryShippingMethods($shipping_data);
            $hd_shipping_methods = $shipping_methods;
          }

          // If no shipping method.
          if (isset($shipping_methods['error'])) {
            $this->logger->notice('Error while shipping update manual for HD. Data: @data Cart: @cart_id Error message: @error_message', [
              '@data' => json_encode($request_content),
              '@cart_id' => $this->cart->getCartId(),
              '@error_message' => $shipping_methods['error_message'],
            ]);
            return new JsonResponse($shipping_methods);
          }

          $shipping_info['carrier_info'] = [
            'code' => $shipping_methods[0]['carrier_code'],
            'method' => $shipping_methods[0]['method_code'],
          ];

          $this->logger->notice('Shipping update manual for HD. Data: @data Address: @address Cart: @cart_id', [
            '@address' => json_encode($shipping_info),
            '@data' => json_encode($request_content),
            '@cart_id' => $this->cart->getCartId(),
          ]);
          $cart = $this->cart->addShippingInfo($shipping_info, $action, $update_billing);
          if ($cart && !empty($cart['shipping']) && !empty($hd_shipping_methods)) {
            $cart['shipping']['methods'] = $hd_shipping_methods;
          }
        }
        break;

      case CartActions::CART_BILLING_UPDATE:
        $billing_info = $request_content['billing_info'];
        $billing_data = $this->cart->formatAddressForShippingBilling($billing_info);
        $this->logger->notice('Billing update manual. Address: @address Data: @data Cart: @cart_id', [
          '@address' => json_encode($billing_data),
          '@data' => json_encode($billing_info),
          '@cart_id' => $this->cart->getCartId(),
        ]);
        $cart = $this->cart->updateBilling($billing_data);
        break;

      case CartActions::CART_PAYMENT_FINALISE:
        // Fetch fresh cart from magento.
        $cart = $this->cart->getCart(TRUE);
        $is_error = FALSE;

        $error_message = 'Delivery Information is incomplete. Please update and try again.';
        $error_code = CartErrorCodes::CART_ORDER_PLACEMENT_ERROR;

        if (is_array($cart)
          && $this->cart->isCartHasOosItem($cart)) {
          $is_error = TRUE;
          $this->logger->error('Error while finalizing payment. Cart has an OOS item. Cart: @cart.', [
            '@cart' => json_encode($cart),
          ]);

          $error_message = 'Cart contains some items which are not in stock.';
          $error_code = CartErrorCodes::CART_HAS_OOS_ITEM;
        }
        // Check if shipping method is present else throw error.
        elseif (empty($cart['shipping']['method'])) {
          $is_error = TRUE;
          $this->logger->error('Error while finalizing payment. No shipping method available. Cart: @cart.', [
            '@cart' => json_encode($cart),
          ]);
        }
        // If shipping address not have custom attributes.
        elseif (empty($cart['shipping']['address']['custom_attributes'])) {
          $is_error = TRUE;
          $this->logger->error('Error while finalizing payment. Shipping address not contains all info. Cart: @cart.', [
            '@cart' => json_encode($cart),
          ]);
        }
        // If address extension attributes doesn't contain all the required
        // fields or required field value is empty, not process/place order.
        elseif (!$this->cart->isAddressExtensionAttributesValid($cart)) {
          $is_error = TRUE;
          $this->logger->error('Error while finalizing payment. Shipping address not contains all required extension attributes. Cart: @cart.', [
            '@cart' => json_encode($cart),
          ]);
        }
        // If first/last name not available in shipping address.
        elseif (empty($cart['shipping']['address']['firstname'])
          || empty($cart['shipping']['address']['lastname'])) {
          $is_error = TRUE;
          $this->logger->error('Error while finalizing payment. First name or Last name not available in cart for shipping address. Cart: @cart.', [
            '@cart' => json_encode($cart),
          ]);
        }
        // If first/last name not available in billing address.
        elseif (empty($cart['cart']['billing_address']['firstname'])
          || empty($cart['cart']['billing_address']['lastname'])) {
          $is_error = TRUE;
          $this->logger->error('Error while finalizing payment. First name or Last name not available in cart for billing address. Cart: @cart.', [
            '@cart' => json_encode($cart),
          ]);
        }

        // If error.
        if ($is_error) {
          return new JsonResponse([
            'error' => TRUE,
            'error_code' => $error_code,
            'message' => $error_message,
          ]);
        }

        $extension = [
          'attempted_payment' => 1,
        ];

        try {
          try {
            $request_content['payment_info']['payment']['additional_data'] = $this->cart->processPaymentData(
              $request_content['payment_info']['payment']['method'],
              $request_content['payment_info']['payment']['additional_data']
            );
          }
          catch (\Exception $e) {
            if ($e->getCode() === 302) {
              // Set attempted payment 1 before redirecting.
              $payment = [
                'method' => $request_content['payment_info']['payment']['method'],
                // For now we still want only the payment method to be set
                // in Magento.
                'additional_data' => [],
              ];

              $this->logger->notice('Calling update payment for finalize exception. Cart id: @cart_id Method: @method', [
                '@cart_id' => $this->cart->getCartId(),
                '@method' => $request_content['payment_info']['payment']['method'],
              ]);
              $updated_cart = $this->cart->updatePayment($payment, $extension);

              if (empty($updated_cart)) {
                throw new \Exception('Update cart failed', 404);
              }
              elseif (!empty($updated_cart['error'])) {
                throw new \Exception($updated_cart['error_message'] ?? '', $updated_cart['error_code'] ?? 404);
              }

              return new JsonResponse([
                'success' => TRUE,
                'redirectUrl' => $e->getMessage(),
              ]);
            }

            throw $e;
          }
        }
        catch (\Exception $e) {
          $this->logger->error('Error during payment finalization. Error message: @message, code: @code', [
            '@message' => $e->getMessage(),
            '@code' => $e->getCode(),
          ]);

          if ($e->getCode() == 404) {
            return new JsonResponse($this->utility->getErrorResponse('Invalid cart', '404'));
          }

          // Cancel reservation when process failed for not enough data,
          // or bad data. i.e. checkout.com cvv missing.
          $this->cart->cancelCartReservation($e->getMessage());

          return new JsonResponse([
            'error' => TRUE,
            'message' => $e->getMessage(),
          ]);
        }

        // Additional changes for VAULT.
        switch ($request_content['payment_info']['payment']['method']) {
          case 'checkout_com':
            if (!empty($request_content['payment_info']['payment']['additional_data']['public_hash'])) {
              $request_content['payment_info']['payment']['method'] = APIWrapper::CHECKOUT_COM_VAULT_METHOD;
            }
            break;

          case 'checkout_com_upapi':
            if (!empty($request_content['payment_info']['payment']['additional_data']['public_hash'])) {
              $request_content['payment_info']['payment']['method'] = APIWrapper::CHECKOUT_COM_UPAPI_VAULT_METHOD;
            }
            break;

        }

        $this->logger->notice('Calling update payment for finalize exception2. Cart id: @cart_id Method: @method', [
          '@cart_id' => $this->cart->getCartId(),
          '@method' => $request_content['payment_info']['payment']['method'],
        ]);

        $cart = $this->cart->updatePayment($request_content['payment_info']['payment'], $extension);
        break;

      case CartActions::CART_PAYMENT_UPDATE:
        $extension = [];
        $user_id = $this->cart->getDrupalInfo('uid');
        if (isset($request_content['payment_info']['payment']['analytics'])) {
          $extension['ga_client_id'] = $request_content['payment_info']['payment']['analytics']['clientId'] ?? '';
          $extension['tracking_id'] = $request_content['payment_info']['payment']['analytics']['trackingId'] ?? '';
          $extension['user_id'] = $user_id > 0 ? $this->cart->getCartCustomerId() : '0';
          $extension['user_type'] = $user_id > 0 ? 'Logged in User' : 'Guest User';
          $extension['user_agent'] = $this->request->headers->get('User-Agent', '');
          $extension['client_ip'] = $_ENV['AH_CLIENT_IP'] ?? $this->request->getClientIp();
          $extension['attempted_payment'] = 1;
        }

        $this->logger->notice('Calling update payment for payment_update. Cart id: @cart_id Method: @method', [
          '@cart_id' => $this->cart->getCartId(),
          '@method' => $request_content['payment_info']['payment']['method'],
        ]);
        $cart = $this->cart->updatePayment($request_content['payment_info']['payment'], $extension);
        break;

      case CartActions::CART_REFRESH:
        // If cart id in request not matches with what in session.
        if ($request_content['cart_id'] != $this->cart->getCartId()) {
          $this->logger->error('Error while cart refresh. Cart data in request not matches with cart in session. Request data: @request_data CartId in session: @cart_id', [
            '@request_data' => json_encode($request_content),
            '@cart_id' => $this->cart->getCartId(),
          ]);
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
    $cart = in_array($action, CartActions::CART_CHECKOUT_ACTIONS)
      ? $this->getProcessedCheckoutData($cart)
      : $this->cart->getProcessedCartData($cart);

    return new JsonResponse($cart);
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
    if (!isset($request_content['data']) || empty($this->cart->getCartId())) {
      $this->logger->error('Trying to place order with either invalid request data or invalid cart. Request data:@data CartId:@cart_id', [
        '@data' => json_encode($request_content['data']),
        '@cart_id' => $this->cart->getCartId(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse('Invalid request', '500'));
    }

    $result = $this->cart->placeOrder($request_content['data']);

    if (!isset($result['error'])) {
      // If redirectUrl is set, it means we need to redirect user to that url
      // in order to complete the payment.
      $response = [
        'success' => TRUE,
        'redirectUrl' => $result['redirect_url'] ?? 'checkout/confirmation?id=' . $result['secure_order_id'],
        'isAbsoluteUrl' => isset($result['redirect_url']),
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
   * @return int
   *   200 if valid, other error codes if otherwise.
   */
  private function validateRequestData(array $request_content) {
    // If action info or cart id not available.
    if (empty($request_content['action'])) {
      $this->logger->error('Cart update operation not containing any action.');
      return 400;
    }

    // For new cart request, we don't need any further validations.
    // Or if request has cart id but cart not exist in session,
    // create new cart for the user.
    if ($request_content['action'] === CartActions::CART_ADD_ITEM
      && (empty($request_content['cart_id']) || !$this->cart->getCartId())) {
      return 200;
    }

    // For any cart update operation, cart should be available in session.
    if (!$this->cart->getCartId()) {
      $this->logger->error('Trying to do cart update operation while cart is not available in session. Data: @request_data', [
        '@request_data' => json_encode($request_content),
      ]);
      return 404;
    }

    // Backend validation.
    $uid = (int) $this->cart->getDrupalInfo('uid');
    $session_customer_id = $this->cart->getDrupalInfo('customer_id');
    $cart_customer_id = $this->cart->getCartCustomerId();
    if ($uid > 0) {
      if (empty($cart_customer_id)) {
        // @todo Check if we should associate cart and proceed.
        return 400;
      }

      // This is serious.
      if ($cart_customer_id !== $session_customer_id) {
        $this->logger->error('Mismatch session customer id:@session_customer_id and card customer id:@cart_customer_id.', [
          '@session_customer_id' => $session_customer_id,
          '@cart_customer_id' => $cart_customer_id,
        ]);
        return 400;
      }
    }

    return 200;
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
        $this->logger->error('Error while associating cart to customer. No cart available in session');
        return new JsonResponse($this->utility->getErrorResponse('No cart in session', 404));
      }

      $customer = $this->drupal->getSessionCustomerInfo();

      if (empty($customer)) {
        $this->logger->error('Error while associating cart to customer. No customer available in session');
        return new JsonResponse($this->utility->getErrorResponse('No user in session', 404));
      }

      // Check if association is not required.
      if ($customer['customer_id'] === $this->cart->getCartCustomerId()) {
        return $this->getCart();
      }

      $this->cart->associateCartToCustomer($customer['customer_id'], TRUE);
    }
    catch (\Exception $e) {
      $this->logger->error('Error while associating cart to customer. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }

    return $this->getCart();
  }

  /**
   * Fetch stores for the current cart for given lat and lng.
   *
   * @param float $lat
   *   The latitude.
   * @param float $lon
   *   The longitude.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return stores list or error message.
   */
  public function getCncStores(float $lat, float $lon) {
    if (empty($this->cart->getCartId())) {
      $this->logger->error('Error while fetching click and collect stores. No cart available in session');
      return new JsonResponse(
        $this->utility->getErrorResponse('No cart in session', 404)
      );
    }

    try {
      $result = $this->cart->getCartStores($lat, $lon);
      return new JsonResponse($result);
    }
    catch (\Exception $e) {
      $this->logger->error('Error while fetching store for cart @cart of @lat, @lng. Error message: @message', [
        '@cart' => $this->cart->getCartId(),
        '@lat' => $lat,
        '@lng' => $lon,
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse(
        $this->utility->getErrorResponse($e->getMessage(), $e->getCode())
      );
    }
  }

  /**
   * API to allow placing order from Drupal.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   */
  public function placeOrderSystem(Request $request) {
    // Confirm the request is from Drupal.
    $secret = $request->headers->get('alshaya-middleware') ?? '';
    if ($secret !== md5($this->settings->getSettings('middleware_auth'))) {
      throw new AccessDeniedHttpException();
    }

    // Additional data request to mimic API call from user.
    $request_content = json_decode($request->getContent(), TRUE);
    if (empty($request_content['cart_id'])) {
      throw new NotFoundHttpException();
    }

    $this->cart->setCartId((int) $request_content['cart_id']);
    return $this->placeOrder($request);
  }

}
