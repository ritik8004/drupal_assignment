<?php

namespace App\Service;

use App\Service\CheckoutCom\APIWrapper;
use App\Service\Config\SystemSettings;
use App\Service\Drupal\Drupal;
use App\Service\Knet\KnetHelper;
use App\Service\Magento\MagentoApiWrapper;
use App\Service\Magento\MagentoInfo;
use App\Service\Magento\CartActions;
use App\Service\CheckoutCom\CustomerCards;
use Doctrine\DBAL\Connection;
use Drupal\alshaya_spc\Helper\SecureText;
use Psr\Log\LoggerInterface;
use Drupal\alshaya_master\Helper\SortUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\PdoStore;

/**
 * Class Cart methods.
 */
class Cart {

  /**
   * Static cache for cart.
   *
   * @var array
   */
  protected static $cart = [];

  /**
   * Stock info that we get from the refresh stock response.
   *
   * @var array
   */
  public static $stockInfo = [];

  /**
   * The cart storage key.
   */
  const SESSION_STORAGE_KEY = 'middleware_cart_id';

  /**
   * Magento service.
   *
   * @var \App\Service\Magento\MagentoInfo
   */
  protected $magentoInfo;

  /**
   * Magento API Wrapper.
   *
   * @var \App\Service\Magento\MagentoApiWrapper
   */
  protected $magentoApiWrapper;

  /**
   * Utility.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * Checkout.com API Wrapper.
   *
   * @var \App\Service\CheckoutCom\APIWrapper
   */
  protected $checkoutComApi;

  /**
   * K-Net Helper.
   *
   * @var \App\Service\Knet\KnetHelper
   */
  protected $knetHelper;

  /**
   * Payment Data provider.
   *
   * @var \App\Service\PaymentData
   */
  protected $paymentData;

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

  /**
   * Service for session.
   *
   * @var \App\Service\SessionStorage
   */
  protected $session;

  /**
   * Session cache.
   *
   * @var \App\Service\SessionCache
   */
  protected $cache;

  /**
   * Checkout.com API Wrapper.
   *
   * @var \App\Service\CheckoutCom\CustomerCards
   */
  protected $customerCards;

  /**
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * Orders service.
   *
   * @var \App\Service\Orders
   */
  protected $orders;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Database connection.
   *
   * @var \Doctrine\DBAL\Connection
   */
  protected $connection;

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Cart constructor.
   *
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API Wrapper.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   * @param \App\Service\CheckoutCom\APIWrapper $checkout_com_api
   *   Checkout.com API Wrapper.
   * @param \App\Service\Knet\KnetHelper $knet_helper
   *   K-Net Helper.
   * @param \App\Service\PaymentData $payment_data
   *   Payment Data provider.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   * @param \App\Service\SessionStorage $session
   *   Service for session.
   * @param \App\Service\SessionCache $cache
   *   Session Cache.
   * @param \App\Service\CheckoutCom\CustomerCards $customer_cards
   *   Checkout.com API Wrapper.
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \App\Service\Orders $orders
   *   Orders service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \Doctrine\DBAL\Connection $connection
   *   Database connection.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   RequestStack Object.
   */
  public function __construct(
    MagentoInfo $magento_info,
    MagentoApiWrapper $magento_api_wrapper,
    Utility $utility,
    APIWrapper $checkout_com_api,
    KnetHelper $knet_helper,
    PaymentData $payment_data,
    SystemSettings $settings,
    SessionStorage $session,
    SessionCache $cache,
    CustomerCards $customer_cards,
    Drupal $drupal,
    Orders $orders,
    LoggerInterface $logger,
    Connection $connection,
    RequestStack $requestStack
  ) {
    $this->magentoInfo = $magento_info;
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->utility = $utility;
    $this->checkoutComApi = $checkout_com_api;
    $this->knetHelper = $knet_helper;
    $this->paymentData = $payment_data;
    $this->settings = $settings;
    $this->session = $session;
    $this->cache = $cache;
    $this->customerCards = $customer_cards;
    $this->drupal = $drupal;
    $this->orders = $orders;
    $this->logger = $logger;
    $this->connection = $connection;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * Wrapper function to get cart id from session.
   *
   * @return int|null
   *   Cart id.
   */
  public function getCartId() {
    return $this->session->getDataFromSession(self::SESSION_STORAGE_KEY);
  }

  /**
   * Wrapper function to set cart id in session.
   *
   * @param int $cart_id
   *   Cart id.
   */
  public function setCartId(int $cart_id) {
    $this->session->updateDataInSession(Cart::SESSION_STORAGE_KEY, $cart_id);
  }

  /**
   * Wrapper function to get coupon applied in cart.
   *
   * @return string
   *   Coupon code or empty string.
   */
  public function getCoupon() {
    $cart = $this->getCart();

    if (empty($cart) || empty($cart['totals'])) {
      return '';
    }

    return $cart['totals']['coupon_code'] ?? '';
  }

  /**
   * Wrapper function to get specific cart item.
   *
   * @param string $sku
   *   SKU.
   *
   * @return array|null
   *   Cart item array or NULL if item not found.
   */
  public function getCartItem(string $sku) {
    $cart = $this->getCart();

    if (empty($cart) || empty($cart['cart']) || empty($cart['cart']['items'])) {
      return NULL;
    }

    foreach ($cart['cart']['items'] ?? [] as $item) {
      if ($item['sku'] === $sku) {
        return $item;
      }
    }

    return NULL;
  }

  /**
   * Get cart by cart id.
   *
   * @param bool $force
   *   True to load data from api, false from cache.
   *
   * @return array
   *   Cart data.
   */
  public function getCart($force = FALSE) {
    if (!empty(static::$cart) && !$force) {
      return static::$cart;
    }

    $cart_id = $this->getCartId();
    if (empty($cart_id)) {
      return NULL;
    }

    // If cart is available in cache.
    if (!$force && !empty($cached_cart = $this->getCartFromCache())) {
      static::$cart = $cached_cart;
      return static::$cart;
    }

    $url = sprintf('carts/%d/getCart', $cart_id);

    try {
      $updated_cart = $this->magentoApiWrapper->doRequest('GET', $url);

      if ($updated_cart === FALSE) {
        throw new \Exception('Cart no longer available', 404);
      }
      elseif (!is_array($updated_cart)) {
        $this->logger->error('Invalid cart data in response received for get cart. ID: @id, Response: @response', [
          '@id' => $cart_id,
          '@response' => json_encode($updated_cart),
        ]);

        throw new \Exception('Invalid cart data in response', 500);
      }

      static::$cart = $updated_cart;

      // Store cart object in cache.
      $this->setCartInCache(static::$cart);

      static::$cart = $this->formatCart(static::$cart);
      return static::$cart;
    }
    catch (\Exception $e) {
      static::$cart = NULL;

      $this->logger->error('Error while getting cart from MDC. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);

      if ($e->getCode() == 404 || strpos($e->getMessage(), 'No such entity with cartId') > -1) {
        $this->removeCartFromSession();
      }

      // Fetch cart from cache (even if stale).
      if (!empty($cached_cart = $this->getCartFromCache(TRUE))) {
        // Setting flag for stale cart cache.
        $cached_cart['stale_cart'] = TRUE;
        static::$cart = $cached_cart;
        return static::$cart;
      }

      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Restore shipping info and get cart.
   *
   * @return array
   *   Cart data.
   */
  public function getRestoredCart() {
    $cart = $this->getCart();
    $this->resetCartCache();
    return $cart;
  }

  /**
   * Create a new cart and get cart id.
   *
   * @return mixed
   *   Cart id.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function createCart(int $customer_id = 0) {
    if (!empty($this->getCartId())) {
      // Validate the cart again to ensure session data is not corrupt.
      $data = $this->getCart();
      if (empty($data['error'])) {
        return $this->getCartId();
      }
    }

    $url = $customer_id > 0
      ? str_replace('{customerId}', $customer_id, 'customers/{customerId}/carts')
      : 'carts';

    try {
      $cart_id = (int) $this->magentoApiWrapper->doRequest('POST', $url);

      // Store cart id in session.
      $this->session->updateDataInSession(self::SESSION_STORAGE_KEY, $cart_id);

      $this->logger->notice('New cart created: @cart_id, customer_id: @customer_id', [
        '@cart_id' => $cart_id,
        '@customer_id' => $customer_id,
      ]);

      return $cart_id;
    }
    catch (\Exception $e) {
      $this->logger->error('Error while creating cart on MDC. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);

      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Add/Update/Remove item in cart.
   *
   * @param string $sku
   *   Sku.
   * @param int|null $quantity
   *   Quantity.
   * @param string $action
   *   Action to be performed (add/update/remove).
   * @param array $options
   *   Options array.
   * @param string $variant_sku
   *   The variant sku value.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function addUpdateRemoveItem(string $sku, ?int $quantity, string $action, array $options = [], string $variant_sku = NULL) {
    $option_data = [];

    if ($action == CartActions::CART_REMOVE_ITEM) {
      $item = $this->getCartItem($sku);

      // Do nothing if item no longer available.
      if (empty($item)) {
        return $this->getCart();
      }
    }

    // If options data available.
    if (!empty($options)) {
      foreach ($options as &$op) {
        $op = (object) $op;
      }
      $option_data = [
        'extension_attributes' => (object) [
          'configurable_item_options' => $options,
        ],
      ];
    }
    $data['items'][] = (object) [
      'sku' => $sku,
      'qty' => $quantity ?? 1,
      'quote_id' => (string) $this->getCartId(),
      'product_option' => (object) $option_data,
      'variant_sku' => $variant_sku,
    ];
    $data['extension'] = (object) [
      'action' => $action,
    ];

    return $this->updateCart($data);
  }

  /**
   * Apply promo on the cart.
   *
   * @param string|null $promo
   *   Promo to apply.
   * @param string $action
   *   Action to perform (promo apply/remove).
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function applyRemovePromo(?string $promo, string $action) {
    $data = [
      'extension' => (object) [
        'action' => $action,
      ],
    ];

    if ($promo) {
      $data['coupon'] = $promo;
    }

    return $this->updateCart($data);
  }

  /**
   * Format shipping info for api call.
   *
   * @param array $shipping_info
   *   Shipping info.
   *
   * @return array
   *   Formatted shipping info for api.
   */
  public function prepareShippingData(array $shipping_info) {
    // If address id available.
    if (!empty($shipping_info['address_id'])) {
      $data['address_id'] = $shipping_info['address_id'];
    }
    else {
      $static_fields = $shipping_info['static'];
      unset($shipping_info['static']);
      $custom_attributes = [];
      foreach ($shipping_info as $field_name => $val) {
        $val = (!is_array($val) && is_null($val))
          ? ''
          : $val;
        $custom_attributes[] = [
          'attribute_code' => $field_name,
          'value' => $val,
        ];
      }

      $fields_data = [];
      foreach ($static_fields as $key => $field) {
        $fields_data[$key] = $field;
      }

      $fields_data = array_merge($fields_data, ['custom_attributes' => $custom_attributes]);
      $data = [
        'address' => $fields_data,
      ];
    }

    return $data;
  }

  /**
   * Adding shipping on the cart.
   *
   * @param array $shipping_data
   *   Shipping address info.
   * @param string $action
   *   Action to perform.
   * @param bool $update_billing
   *   Whether billing needs to be updated or not.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function addShippingInfo(array $shipping_data, string $action, bool $update_billing = TRUE) {
    $data = [
      'extension' => (object) [
        'action' => $action,
      ],
    ];

    // If shipping address add by address id.
    $carrier_info = $shipping_data['carrier_info'];
    $fields_data = !empty($shipping_data['customer_address_id'])
      ? $shipping_data['address']
      : $this->formatAddressForShippingBilling($shipping_data);
    $data['shipping']['shipping_address'] = $fields_data;
    $data['shipping']['shipping_carrier_code'] = $carrier_info['code'];
    $data['shipping']['shipping_method_code'] = $carrier_info['method'];

    $cart = $this->updateCart($data);

    // If cart update has error.
    if ($this->cartHasError($cart)) {
      return $cart;
    }

    // If billing needs to updated or billing is not available added at all
    // in the cart. Assuming if name is not set in billing means billing is
    // not set. City with value 'NONE' means, that this was added in CnC
    // by default and not changed by user.
    if ($update_billing
      || (empty($cart['cart']['billing_address']['firstname'])
        || $cart['cart']['billing_address']['city'] == 'NONE')
    ) {
      $cart = $this->updateBilling($data['shipping']['shipping_address']);
    }

    return $cart;
  }

  /**
   * Check if cart has error or not.
   *
   * @param array $cart
   *   Cart data.
   *
   * @return bool
   *   If cart has error or not.
   */
  public function cartHasError(array $cart) {
    if ((isset($cart['error']) && $cart['error']) || $cart['response_message'][1] == 'json_error') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Format the address array.
   *
   * Format the address array so that it can be used to update billing or
   * shipping address in the cart.
   *
   * @param array $address
   *   Address array.
   *
   * @return array
   *   Formatted address array.
   */
  public function formatAddressForShippingBilling(array $address) {
    $static_fields = $address['static'];
    // Unset static and carrier info if available.
    unset($address['static']);
    if (!empty($address['carrier_info'])) {
      unset($address['carrier_info']);
    }

    $custom_attributes = [];
    foreach ($address as $field_name => $val) {
      $val = (!is_array($val) && is_null($val))
        ? ''
        : $val;
      $custom_attributes[] = [
        'attributeCode' => $field_name,
        'value' => $val,
      ];
    }

    $fields_data = [];
    foreach ($static_fields as $key => $field) {
      $fields_data[$key] = $field;
    }

    $fields_data = array_merge($fields_data, ['customAttributes' => $custom_attributes]);
    if (!empty($address['street'])) {
      $fields_data['street'] = is_array($address['street'])
        ? $address['street']
        : [$address['street']];
    }

    return $fields_data;
  }

  /**
   * Add click n collect shipping on the cart.
   *
   * @param array $shipping_data
   *   Shipping address info.
   * @param string $action
   *   Action to perform.
   * @param bool $update_billing
   *   Whether billing needs to update or not.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function addCncShippingInfo(array $shipping_data, string $action, bool $update_billing = TRUE) {
    $data = [
      'extension' => (object) [
        'action' => $action,
      ],
    ];
    $store = $shipping_data['store'];
    $static_fields = array_merge($shipping_data['store']['cart_address'], $shipping_data['static']);
    // Unset as not needed in further processing.
    unset($static_fields['extension']);
    $carrier_info = $shipping_data['carrier_info'];

    $shipping_data = array_merge($shipping_data, $shipping_data['store']['cart_address']['extension']);
    // Unset as not needed in further processing.
    unset($shipping_data['carrier_info'], $shipping_data['static'], $shipping_data['store']);

    $custom_attributes = [];
    foreach ($shipping_data as $field_name => $val) {
      $custom_attributes[] = [
        'attributeCode' => $field_name,
        'value' => $val,
      ];
    }

    $fields_data = [];
    foreach ($static_fields as $key => $field) {
      $field = (!is_array($field) && is_null($field))
        ? ''
        : $field;
      $fields_data[$key] = ($key == 'street') ? [$field] : $field;
    }

    $fields_data = array_merge($fields_data, ['custom_attributes' => $custom_attributes]);
    $data['shipping']['shipping_address'] = $fields_data;
    $data['shipping']['shipping_carrier_code'] = $carrier_info['code'];
    $data['shipping']['shipping_method_code'] = $carrier_info['method'];
    $data['shipping']['extension_attributes'] = (object) [
      'click_and_collect_type' => !empty($store['rnc_available']) ? 'reserve_and_collect' : 'ship_to_store',
      'store_code' => $store['code'],
    ];

    $cart = $this->updateCart($data);

    // If cart update has error.
    if ($this->cartHasError($cart)) {
      return $cart;
    }

    if (empty($cart['cart']['billing_address']['city'])
      || $cart['cart']['billing_address']['city'] == 'NONE') {
      // Setting city value as 'NONE' so that, we can
      // identify if billing address added is default one and
      // not actually added by the customer on FE.
      $data['shipping']['shipping_address']['city'] = 'NONE';
      // Adding billing address.
      return $this->updateBilling($data['shipping']['shipping_address']);
    }

    return $cart;
  }

  /**
   * Update billing info on cart.
   *
   * @param array $billing_data
   *   Billing data.
   *
   * @return array
   *   Response data.
   */
  public function updateBilling(array $billing_data) {
    $data = [
      'extension' => (object) [
        'action' => CartActions::CART_BILLING_UPDATE,
      ],
    ];

    unset($billing_data['id']);
    $data['billing'] = $billing_data;

    return $this->updateCart($data);
  }

  /**
   * Adds a customer to cart.
   *
   * @param int $customer_id
   *   Customer id.
   * @param bool $reset_cart
   *   True to Reset cart, otherwise false.
   *
   * @return mixed
   *   Response.
   */
  public function associateCartToCustomer(int $customer_id, bool $reset_cart = FALSE) {
    $cart_id = $this->getCartId();
    if ($reset_cart) {
      $this->getRestoredCart();
    }
    $url = sprintf('carts/%d/associate-cart', $cart_id);

    try {
      $data = [
        'customerId' => $customer_id,
        'cartId' => $cart_id,
        'store_id' => $this->magentoInfo->getMagentoStoreId(),
      ];

      $result = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => (object) $data]);

      // After association restore the cart.
      if ($result) {
        static::$cart = NULL;
        $this->getCart(TRUE);
        return TRUE;
      }

      throw new \Exception('Unable to associate cart', 500);
    }
    catch (\Exception $e) {
      $this->logger->error('Error while associating cart to customer. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Adding payment on the cart.
   *
   * @param array $data
   *   Payment info.
   * @param array $extension
   *   (Optional) Cart extension.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function updatePayment(array $data, array $extension = []) {
    $extension['action'] = CartActions::CART_PAYMENT_UPDATE;

    $update = [
      'extension' => (object) $extension,
    ];

    $update['payment'] = [
      'method' => $data['method'],
      'additional_data' => $data['additional_data'],
    ];

    $expire = (int) $_ENV['CACHE_TIME_LIMIT_PAYMENT_METHOD_SELECTED'];
    if ($expire > 0) {
      $this->cache->set('payment_method', $expire, $data['method']);
    }

    // If upapi payment method (payment method via checkout.com).
    if ($this->isUpapiPaymentMethod($data['method'])) {
      // Add success and fail redirect url to additional data.
      $host = 'https://' . $this->request->getHttpHost() . '/middleware/public/payment/';
      $langcode = $this->request->query->get('lang');
      $update['payment']['additional_data']['successUrl'] = $host . 'success/' . $langcode;
      $update['payment']['additional_data']['failUrl'] = $host . 'error/' . $langcode;
    }

    $old_cart = $this->getCart();
    $cart = $this->updateCart($update);
    if (isset($cart['error_code'])) {
      $error_message = $cart['error_code'] > 600
        ? 'Back-end system is down'
        : $cart['error_message'];

      $message = $this->prepareOrderFailedMessage($old_cart, $data, $error_message, 'update cart', 'NA');
      $this->logger->error('Error occurred while placing order. @message', [
        '@message' => $message,
      ]);
    }

    return $cart;
  }

  /**
   * Checks if upapi payment method (payment method via checkout.com).
   *
   * @param string $payment_method
   *   Payment method code.
   *
   * @return bool
   *   TRUE if payment methods from checkout.com
   */
  public function isUpapiPaymentMethod(string $payment_method) {
    return strpos($payment_method, 'checkout_com_upapi') !== FALSE;
  }

  /**
   * Process payment data before placing order.
   *
   * @param string $method
   *   Payment method.
   * @param array $additional_info
   *   Additional info.
   *
   * @return array
   *   Processed payment data.
   *
   * @throws \Exception
   */
  public function processPaymentData(string $method, array $additional_info) {
    $additional_data = [];

    // Method specific code.
    switch ($method) {
      case 'knet':
        $cart = $this->getCart();

        $response = $this->knetHelper->initKnetRequest(
          $cart['totals']['grand_total'],
          $this->getCartId(),
          $cart['cart']['extension_attributes']['real_reserved_order_id'],
          $this->getCartCustomerId()
        );

        if (isset($response['redirectUrl']) && !empty($response['redirectUrl'])) {
          $response['payment_type'] = 'knet';
          $this->paymentData->setPaymentData($this->getCartId(), $response['id'], $response['data']);
          throw new \Exception($response['redirectUrl'], 302);
        }

        throw new \Exception('Failed to initiate K-Net request.', 500);

      case 'checkout_com_upapi':
        $additional_data = $additional_info;
        break;

      case 'checkout_com':
        $process_3d = FALSE;
        $end_point = '';
        // Process for new 3D card.
        if ($additional_info['card_type'] == 'new') {
          $additional_data = [
            'card_token_id' => $additional_info['id'],
            'udf3' => $additional_info['udf3'],
          ];

          // Validate bin if MADA enabled.
          $additional_data['udf1'] = $this->checkoutComApi->validateMadaBin($additional_info['card']['bin'])
            ? 'MADA'
            : '';

          $process_3d = $additional_data['udf1'] || $this->checkoutComApi->is3dForced();
          $end_point = APIWrapper::ENDPOINT_AUTHORIZE_PAYMENT;
        }
        elseif ($additional_info['card_type'] == 'existing') {
          $card = $this->customerCards->getGivenCardInfo($this->getCartCustomerId(), $additional_info['id']);
          if (($card['mada'] || $this->checkoutComApi->is3dForced()) && empty($additional_info['cvv'])) {
            throw new \Exception('Cvv missing for credit/debit card.', 400);
          }
          elseif ($card['mada'] || $this->checkoutComApi->is3dForced()) {
            $process_3d = TRUE;
            $additional_data = [
              'cardId' => $card['gateway_token'],
              'cvv' => $this->customerCards->deocodePublicHash(urldecode($additional_info['cvv'])),
              'udf1' => $card['mada'] ? 'MADA' : '',
              'udf2' => APIWrapper::CARD_ID_CHARGE,
            ];
            $end_point = APIWrapper::ENDPOINT_CARD_PAYMENT;
          }
          elseif (!$card['mada'] && !$this->checkoutComApi->is3dForced()) {
            $additional_data = [
              'public_hash' => $card['public_hash'],
            ];
          }
        }

        // Process 3D if MADA or 3D Forced.
        if ($process_3d && !empty($additional_data) && !empty($end_point)) {
          $response = $this->checkoutComApi->request3dSecurePayment(
            $this->getCart(),
            $additional_data,
            $end_point
          );

          if (isset($response['responseCode'])
              && $response['responseCode'] == APIWrapper::SUCCESS
              && !empty($response[APIWrapper::REDIRECT_URL])) {
            $response['payment_type'] = 'checkout_com';
            // We will use this again to redirect back to Drupal.
            $response['langcode'] = $this->settings->getRequestLanguage();
            $this->paymentData->setPaymentData($this->getCartId(), $response['id'], $response);
            throw new \Exception($response[APIWrapper::REDIRECT_URL], 302);
          }

          throw new \Exception('Failed to initiate 3D request.', 500);
        }

        // For 2D send the success and fail urls to Magento to allow them
        // to use it when authorising.
        $additional_data['successUrl'] = $this->checkoutComApi->getSuccessUrl();
        $additional_data['failUrl'] = $this->checkoutComApi->getFailUrl();

        break;
    }

    return $additional_data;
  }

  /**
   * Common function for updating cart.
   *
   * @param array $data
   *   Data to update for cart.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function updateCart(array $data) {
    $cart_id = $this->getCartId();
    $url = sprintf('carts/%d/updateCart', $cart_id);

    $cart = NULL;

    $action = isset($data['extension']) && is_array($data['extension'])
      ? $data['extension']['action'] ?? ''
      : $data['extension']->action ?? '';

    // We do not want to send the variant sku values to magento unnecessarily.
    // So we store it separately and remove it from $data.
    $skus = [];
    foreach ($data['items'] as $key => $item) {
      $skus[] = $item->variant_sku ?? $item->sku;
      unset($data['items'][$key]->variant_sku);
    }

    try {
      $cart_updated = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => (object) $data], $action);
      static::$cart = $this->formatCart($cart_updated);
      $cart = static::$cart;

      // If exception at response message level.
      if ($cart['response_message'][1] == 'json_error') {
        $messages = json_decode($cart['response_message'][0], TRUE);
        // Iterate over each message.
        foreach ($messages as $msg) {
          // If message is of OOS.
          if (!empty($exception_type = $this->exceptionType($msg))) {
            $cart['response_message'][0] = $exception_type;
            // Throwing exception so that catch by subsequent catch block.
            throw new \Exception($msg);
          }
        }
      }

      // Set cart in cache.
      $this->setCartInCache($cart_updated);
      return $cart;
    }
    catch (\Exception $e) {
      static::$cart = NULL;

      $this->logger->error('Error while updating cart on MDC for action @action. Error message: @message', [
        '@action' => $action,
        '@message' => $e->getMessage(),
      ]);

      $is_add_to_cart = ($action == CartActions::CART_ADD_ITEM);

      // Re-set cart id in session if exception is for cart not found.
      // Also try to do the same operation again for the user.
      if (strpos($e->getMessage(), 'No such entity with cartId') > -1) {
        $this->removeCartFromSession();

        // Create new cart only if user is trying to add an item to cart.
        if ($is_add_to_cart) {
          $info = $this->drupal->getSessionCustomerInfo();
          $newCart = $this->createCart($info['customer_id'] ?? 0);
          if (empty($newCart['error'])) {
            return $this->updateCart($data);
          }
        }
      }
      else {
        $this->cancelCartReservation($e->getMessage());
      }

      // Get cart object if already not available.
      $cart = !empty($cart) ? $cart : $this->getCart();

      // Check the exception type from drupal.
      $exception_type = $this->exceptionType($e->getMessage());
      // We want to throw error for add to cart action, to display errors
      // if api fails. (We don't need to return cart object as we only care
      // about error whenever we are on pdp / Add to cart form.)
      // Because, If we return cart object, it won't show any error as we are
      // not passing error with cart object, and with successful cart object it
      // will show notification of add to cart (Which we don't need here.).
      // If exception type is of stock limit or of quantity limit,
      // refresh the stock for the sku items in cart from MDC to drupal.
      if (!empty($exception_type) && !$is_add_to_cart) {
        // If cart is available and cart has item.
        if (!empty($cart['cart']['id']) && !empty($cart['cart']['items'])) {
          $response = $this->drupal->triggerCheckoutEvent('validate cart', ['cart' => $cart['cart']]);
          if ($response['status'] == TRUE) {
            if (!empty($response['data']['stock'])) {
              self::$stockInfo = $response['data']['stock'];
            }
            // Return cart object.
            return $cart;
          }
        }
      }
      elseif (!empty($exception_type) && $is_add_to_cart && ($exception_type === 'OOS')) {
        if (!empty($cart['cart']['id']) && !empty($cart['cart']['items'])) {
          $skus = array_merge($skus, array_column($cart['cart']['items'], 'sku'));
        }
        $response = $this->drupal->triggerCheckoutEvent('refresh stock', [
          'skus' => $skus,
        ]);

        if ($response['status'] == TRUE) {
          if (!empty($response['data']['stock'])) {
            self::$stockInfo = $response['data']['stock'];
          }
        }
      }

      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Get the exception message type.
   *
   * @param string $message
   *   Exception message.
   *
   * @return null|string
   *   Message type.
   */
  public function exceptionType(string $message) {
    $exception_messages = $this->settings->getSettings('alshaya_spc.exception_message');
    if (!empty($exception_messages)) {
      foreach ($exception_messages as $msg => $message_type) {
        // If message matches.
        if (strpos($message, $msg) !== FALSE) {
          return $message_type;
        }
      }
    }

    return NULL;
  }

  /**
   * Gets shipping methods.
   *
   * @param array $data
   *   Data for getting shipping method.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getHomeDeliveryShippingMethods(array $data) {
    static $static;

    if (empty($data['address']['country_id'])) {
      $this->logger->error('Error in getting shipping methods for HD as country id not available. Data:@data', [
        '@data' => json_encode($data),
      ]);
      return [];
    }

    $key = md5(json_encode($data['address']));
    if (isset($static[$key])) {
      return $static[$key];
    }

    $expire = (int) $_ENV['CACHE_TIME_LIMIT_DELIVERY_METHODS'];
    $cache = $expire > 0 ? $this->cache->get('delivery_methods') : NULL;
    if (isset($cache) && $cache['key'] === $key) {
      $static[$key] = $cache['methods'];
      return $static[$key];
    }

    $url = sprintf('carts/%d/estimate-shipping-methods', $this->getCartId());

    try {
      $static[$key] = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => $data]);

      $static[$key] = array_filter($static[$key], function ($method) {
        return ($method['carrier_code'] !== 'click_and_collect');
      });
    }
    catch (\Exception $e) {
      $this->logger->error('Error while getting shipping methods for HD. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }

    // Resetting the array keys or key might not start with 0 if first method is
    // cnc related and we filter it out.
    $static[$key] = array_values($static[$key]);

    $cache = [
      'key' => $key,
      'methods' => $static[$key],
    ];

    if ($expire > 0) {
      $this->cache->set('delivery_methods', $expire, $cache);
    }
    return $static[$key];
  }

  /**
   * Gets payment methods.
   *
   * @return array
   *   Payment method list.
   */
  public function getPaymentMethods() {
    static $static;

    $cart = $this->getCart();
    $type = $cart['shipping']['method'] ?? '';

    if (empty($type)) {
      $this->logger->error('Error while getting payment methods from MDC. Shipping method not available in cart. Cart:@cart', [
        '@cart' => json_encode($cart),
      ]);
      return NULL;
    }

    $key = 'payment_methods_' . $type;
    if (isset($static[$key])) {
      return $static[$key];
    }

    $url = sprintf('carts/%d/payment-methods', $this->getCartId());

    try {
      $static[$key] = $this->magentoApiWrapper->doRequest('GET', $url);
    }
    catch (\Exception $e) {
      $this->logger->error('Error while getting payment methods from MDC. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }

    return $static[$key];
  }

  /**
   * Get the payment method set on cart.
   *
   * @return string
   *   Payment method set on cart.
   */
  public function getPaymentMethodSetOnCart() {
    $expire = (int) $_ENV['CACHE_TIME_LIMIT_PAYMENT_METHOD_SELECTED'];
    if ($expire > 0 && ($method = $this->cache->get('payment_method'))) {
      return $method;
    }

    $url = sprintf('carts/%d/selected-payment-method', $this->getCartId());
    try {
      $result = $this->magentoApiWrapper->doRequest('GET', $url);
      return $result['method'] ?? NULL;
    }
    catch (\Exception $e) {
      $this->logger->error('Error while getting payment set on cart. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Place order.
   *
   * @param array $data
   *   Post data.
   *
   * @return array
   *   Status.
   */
  public function placeOrder(array $data) {
    $url = sprintf('carts/%d/order', $this->getCartId());
    $cart = $this->getCart();

    // Check if shiping method is present else throw error.
    if (empty($cart['shipping']['method'])) {
      $this->logger->error('Error while placing order. No shipping method available. Cart: @cart.', [
        '@cart' => json_encode($cart),
      ]);
      return $this->utility->getErrorResponse('Delivery Information is incomplete. Please update and try again.', 505);
    }

    // Check if shipping address not have custom attributes.
    if (empty($cart['shipping']['address']['custom_attributes'])) {
      $this->logger->error('Error while placing order. Shipping address not contains all info. Cart: @cart.', [
        '@cart' => json_encode($cart),
      ]);
      return $this->utility->getErrorResponse('Delivery Information is incomplete. Please update and try again.', 505);
    }

    // If address extension attributes doesn't contain all the required fields
    // or required field value is empty, not process/place order.
    if (!$this->isAddressExtensionAttributesValid($cart)) {
      $this->logger->error('Error while placing order. Shipping address not contains all address extension attributes. Cart: @cart.', [
        '@cart' => json_encode($cart),
      ]);
      return $this->utility->getErrorResponse('Delivery Information is incomplete. Please update and try again.', 505);
    }

    // If first/last name not available in shipping address.
    if (empty($cart['shipping']['address']['firstname'])
      || empty($cart['shipping']['address']['lastname'])) {
      $this->logger->error('Error while placing order. First name or Last name not available in cart for shipping address. Cart: @cart.', [
        '@cart' => json_encode($cart),
      ]);
      return $this->utility->getErrorResponse('Delivery Information is incomplete. Please update and try again.', 505);
    }

    // If first/last name not available in billing address.
    if (empty($cart['cart']['billing_address']['firstname'])
      || empty($cart['cart']['billing_address']['lastname'])) {
      $this->logger->error('Error while placing order. First name or Last name not available in cart for billing address. Cart: @cart.', [
        '@cart' => json_encode($cart),
      ]);
      return $this->utility->getErrorResponse('Delivery Information is incomplete. Please update and try again.', 505);
    }

    $lock = FALSE;
    $settings = $this->settings->getSettings('spc_middleware');
    $checkout_settings = $this->settings->getSettings('alshaya_checkout_settings');

    // Check if cart total is valid return with an error message.
    if (!($this->isCartTotalValid($cart, $checkout_settings))) {
      $this->logger->error('Error while placing order. Cart total is not valid for cart: @cart.', [
        '@cart' => json_encode($cart),
      ]);
      return $this->utility->getErrorResponse('Sorry, something went wrong and we are unable to process your request right now. Please try again later.', 500);
    }

    // Check whether order locking is enabled.
    if (!isset($settings['spc_middleware_lock_place_order']) || $settings['spc_middleware_lock_place_order'] == TRUE) {
      $lock_store = new PdoStore($this->connection);
      $lock_factory = new Factory($lock_store);

      $lock_name = 'spc_place_order_' . $this->getCartId();
      $lock = $lock_factory->createLock($lock_name);

      if (!$lock->acquire()) {
        $this->logger->error('Could not acquire lock to place SPC order: @lock_name"', [
          '@lock_name' => $lock_name,
        ]);
        return $this->utility->getErrorResponse('Sorry, we were able to complete your purchase but something went wrong and we could not display the order confirmation page. Please review your past orders or contact our customer service team for assistance.', 700);
      }
    }

    try {
      $request_options = [
        'timeout' => $checkout_settings['place_order_timeout'],
      ];

      // We don't pass any payment data in place order call to MDC because its
      // optional and this also sets in ACM MDC observer.
      $this->logger->notice('Place order initiated for Cart: @cart Data: @data', [
        '@cart' => json_encode($cart),
        '@data' => json_encode($data),
      ]);
      $result = $this->magentoApiWrapper->doRequest('PUT', $url, $request_options);

      if (!empty($lock)) {
        $lock->release();
      }

      if (!empty($result['redirect_url'])) {
        return $result;
      }

      $order_id = (int) str_replace('"', '', $result);

      $this->logger->notice('Order placed successfully. Cart: @cart Orderid: @order_id', [
        '@cart' => json_encode($cart),
        '@order_id' => $order_id,
      ]);

      return $this->processPostOrderPlaced($order_id, $data['paymentMethod']['method']);
    }
    catch (\Exception $e) {
      // Handle checkout.com 2D exception.
      if ($this->exceptionType($e->getMessage()) === 'FRAUD') {
        $this->logger->notice('Magento returned fraud exception . Error message: @message', [
          '@message' => $e->getMessage(),
        ]);
        return $this->handleCheckoutComRedirection();
      }

      $double_check_done = 'no';
      $cartReservedOrderId = $cart['cart']['extension_attributes']['real_reserved_order_id'];

      $doubleCheckEnabled = $checkout_settings['place_order_double_check_after_exception'];
      if ($doubleCheckEnabled) {
        $double_check_done = 'yes';
        try {
          $lastOrder = $this->orders->getLastOrder((int) $this->getCartCustomerId());

          if ($lastOrder && $cartReservedOrderId === $lastOrder['increment_id']) {
            $this->logger->warning('Place order failed but order was placed, we will move forward. Message: @message, Reserved order id: @order_id, Cart id: @cart_id', [
              '@message' => $e->getMessage(),
              '@order_id' => $cartReservedOrderId,
              '@cart_id' => $cart['cart']['id'],
            ]);

            return $this->processPostOrderPlaced((int) $lastOrder['order_id'], $data['paymentMethod']['method']);
          }
        }
        catch (\Exception $doubleException) {
          $this->logger->error('Error occurred while trying to double check. Exception: @message', [
            '@message' => $doubleException->getMessage(),
          ]);
        }
      }

      // UPAPI has cart locking mechanism, we do not need cancel reservation.
      if (!$this->isUpapiPaymentMethod($data['paymentMethod']['method'])) {
        $this->cancelCartReservation($e->getMessage());
      }

      $error_message = $e->getCode() > 600
        ? 'Back-end system is down'
        : $e->getMessage();
      $message = $this->prepareOrderFailedMessage($cart, $data, $error_message, 'place order', $double_check_done);
      $this->logger->error('Error occurred while placing order. @message', [
        '@message' => $message,
      ]);

      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Validates the extension attributes of the address of the cart.
   *
   * @param array $cart
   *   Cart data.
   *
   * @return bool
   *   FALSE if empty field value.
   */
  public function isAddressExtensionAttributesValid(array $cart) {
    $is_valid = TRUE;
    // If there are address fields available for validation
    // in drupal settings.
    if (!empty($address_fields_to_validate = $this->cartAddressFieldsToValidate())) {
      $cart_address_custom = [];
      // Prepare cart address field data.
      foreach ($cart['shipping']['address']['custom_attributes'] as $cart_custom_attributes) {
        $cart_address_custom[$cart_custom_attributes['attribute_code']] = $cart_custom_attributes['value'];
      }

      // Check each required field in custom attributes available in cart
      // shipping address or not.
      foreach ($address_fields_to_validate as $address_field) {
        // If field not exists or empty.
        if (empty($cart_address_custom[$address_field])) {
          $this->logger->error('Field :@field_code not available in cart shipping address. Cart id: @cart_id', [
            '@field_code' => $address_field,
            '@cart_id' => $cart['cart']['id'],
          ]);
          $is_valid = FALSE;
          break;
        }
      }
    }

    return $is_valid;
  }

  /**
   * Get address fields to validate from drupal settings.
   *
   * @see `/factory-hooks/post-settings-php/alshaya_address_fields.php`
   *
   * @return array
   *   Fields to validate.
   */
  public function cartAddressFieldsToValidate() {
    $address_fields_to_validate = [];

    // Get the address fields based on site/country code
    // from the drupal settings.
    $site_country_code = $this->settings->getSettings('alshaya_site_country_code');
    $address_fields = $this->settings->getSettings('alshaya_address_fields');

    // Use default value first if available.
    if (isset($address_fields['default'][$site_country_code['country_code']])) {
      $address_fields_to_validate = $address_fields['default'][$site_country_code['country_code']];
    }

    // If brand specific value available/override.
    if (isset($address_fields[$site_country_code['site_code']])
      || isset($address_fields[$site_country_code['country_code']])) {
      $address_fields_to_validate = $address_fields[$site_country_code['site_code']][$site_country_code['country_code']];
    }

    return $address_fields_to_validate;
  }

  /**
   * Process post order is placed.
   *
   * @param int $order_id
   *   Order ID.
   * @param string $payment_method
   *   Payment method.
   *
   * @return array
   *   Final status array.
   */
  public function processPostOrderPlaced(int $order_id, string $payment_method) {
    $cart = $this->getCart();
    $email = $this->getCartCustomerEmail();

    // Remove cart id and other caches from session.
    $this->removeCartFromSession();

    // Set order in session for later use.
    $this->session->updateDataInSession(Orders::SESSION_STORAGE_KEY, $order_id);

    // Set cart id of the order for later use.
    $this->session->updateDataInSession(Orders::ORDER_CART_ID, $cart['cart']['id']);

    // Post order id and cart data to Drupal.
    $data = [
      'order_id' => (int) $order_id,
      'cart' => $cart['cart'],
      'payment_method' => $payment_method,
    ];

    $this->drupal->triggerCheckoutEvent('place order success', $data);

    return [
      'success' => TRUE,
      'order_id' => $order_id,
      'secure_order_id' => SecureText::encrypt(
        json_encode(['order_id' => $order_id, 'email' => $email]),
        $this->magentoInfo->getMagentoSecretInfo()['consumer_secret']
      ),
    ];
  }

  /**
   * Wrapper function to get cleaned cart data to log.
   *
   * @param array $cart
   *   Cart data.
   *
   * @return string
   *   Cleaned cart data as JSON string.
   */
  public function getCartDataToLog(array $cart) {
    // TODO: Remove sensitive info.
    return json_encode($cart);
  }

  /**
   * Return customer id from current session.
   *
   * @return int|null
   *   Return customer id or null.
   */
  public function getCartCustomerId() {
    $cart = $this->getCart();

    if (isset($cart, $cart['customer'], $cart['customer']['id'])) {
      return $cart['customer']['id'];
    }

    return NULL;
  }

  /**
   * Return customer email from cart in session.
   *
   * @return string|null
   *   Return customer email or null.
   */
  public function getCartCustomerEmail() {
    $cart = $this->getCart();
    return $cart['customer']['email'] ?? NULL;
  }

  /**
   * Cancel cart reservation is required.
   *
   * @param string $message
   *   Message to log for cancelling reservation.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function cancelCartReservation(string $message) {
    $url = 'cancel/reserve/cart';
    $cart = $this->getCart();

    if (empty($cart)) {
      return;
    }

    if ($this->magentoInfo->isCancelReservationEnabled() && $cart['cart']['extension_attributes']['attempted_payment']) {
      $cart_id = $this->getCartId();
      try {
        $data = [
          'quoteId' => $cart_id,
          'message' => $message,
        ];
        $response = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => $data]);
        if (empty($response['status']) || $response['status'] !== 'SUCCESS') {
          throw new \Exception($response['message'] ?? json_encode($response));
        }
      }
      catch (\Exception $e) {
        // Exception handling here.
        $this->logger->error('Error occurred while cancelling reservation for cart id @cart_id, Drupal message: @message, API Response: @response', [
          '@cart_id' => $cart_id,
          '@message' => $message,
          '@response' => $e->getMessage(),
        ]);
        return;
      }

      // Restore cart to get more info about what is wrong in cart.
      $this->getCart(TRUE);
    }
  }

  /**
   * Format the cart data to have better structured array.
   *
   * @param array $cart
   *   Cart response from Magento.
   *
   * @return array
   *   Formatted / processed cart.
   */
  private function formatCart(array $cart) {
    // Move customer data to root level.
    $cart['customer'] = $cart['cart']['customer'] ?? [];
    unset($cart['cart']['customer']);

    foreach ($cart['customer']['addresses'] ?? [] as $key => $address) {
      $cart['customer']['addresses'][$key]['region'] = $address['region_id'];

      $cart['customer']['addresses'][$key]['customer_address_id'] = $address['id'];
      unset($cart['customer']['addresses'][$key]['id']);
    }

    // Format shipping info.
    $cart['shipping'] = $cart['cart']['extension_attributes']['shipping_assignments'][0]['shipping'] ?? [];
    unset($cart['cart']['extension_attributes']['shipping_assignments']);

    $shippingMethod = $cart['shipping']['method'] ?? '';
    $cart['shipping']['type'] = strpos($shippingMethod, 'click_and_collect') !== FALSE
      ? 'click_and_collect'
      : 'home_delivery';

    $cart['shipping']['clickCollectType'] = $cart['shipping']['extension_attributes']['click_and_collect_type'] ?? '';
    $cart['shipping']['storeCode'] = $cart['shipping']['extension_attributes']['store_code'] ?? '';
    unset($cart['shipping']['extension_attributes']);

    // Initialise payment data holder.
    $cart['payment'] = [];
    // When shipping method is empty, Set shipping and billing info to empty,
    // so that we can show empty shipping and billing component in react
    // to allow users to fill addresses.
    if (empty($shippingMethod)) {
      $cart['shipping'] = [];
      $cart['cart']['billing_address'] = [];
    }

    return $cart;
  }

  /**
   * Wrapper function to reset cart cache.
   */
  protected function resetCartCache() {
    $this->cache->delete('delivery_methods');
    $this->cache->delete('payment_methods_home_delivery');
    $this->cache->delete('payment_methods_click_and_collect');
    $this->cache->delete('payment_method');
  }

  /**
   * Wrapper function to remove cart data and cache.
   */
  protected function removeCartFromSession() {
    $this->session->updateDataInSession(self::SESSION_STORAGE_KEY, NULL);
    $this->cache->delete('cached_cart');
    $this->resetCartCache();
  }

  /**
   * Get cart stores from magento.
   *
   * @param float $lat
   *   The latitude.
   * @param float $lon
   *   The longitude.
   *
   * @return array|mixed
   *   Return array of stores.
   *
   * @throws \Exception
   */
  public function getCartStores($lat, $lon) {
    $cart_id = $this->getCartId();
    $endpoint = 'click-and-collect/stores/cart/' . $cart_id . '/lat/' . $lat . '/lon/' . $lon;
    try {
      if (empty($stores = $this->magentoApiWrapper->doRequest('GET', $endpoint, []))) {
        return $stores;
      }

      foreach ($stores as &$store) {
        $store_info = $this->drupal->getStoreInfo($store['code']);
        $store += $store_info;
        $store['formatted_distance'] = number_format((float) $store['distance'], 2, '.', '');
        $store['delivery_time'] = $store['sts_delivery_time_label'];
        if ($store['rnc_available'] && isset($store['rnc_config'])) {
          $store['delivery_time'] = $store['rnc_config'];
        }
        if (isset($store['rnc_config'])) {
          unset($store['rnc_config']);
        }
      }

      // Sort the stores first by distance and then by name.
      SortUtility::sortByMultipleKey($stores, 'rnc_available', 'desc', 'distance', 'asc');
      return $stores;
    }
    catch (\Exception $e) {
      // Exception handling here.
      $this->logger->error('Error occurred while fetching stores for cart id @cart_id, API Response: @response', [
        '@cart_id' => $cart_id,
        '@response' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Get checkout.com data from Magento and prepare 3D verification redirection.
   *
   * @return array
   *   Response.
   */
  private function handleCheckoutComRedirection() {
    $url = sprintf('carts/%d/selected-payment-method', $this->getCartId());
    try {
      $result = $this->magentoApiWrapper->doRequest('GET', $url);
    }
    catch (\Exception $e) {
      $this->logger->error('Error while getting payment set on cart. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }

    $response = $result['extension_attributes']['fraudrule_response'] ?? [];
    if (empty($response)) {
      return $this->utility->getErrorResponse('Transaction failed.', 500);
    }
    $response['langcode'] = $this->settings->getRequestLanguage();
    $response['payment_type'] = 'checkout_com';
    $this->paymentData->setPaymentData($this->getCartId(), $response['id'], $response);

    $this->logger->notice('Redirecting user for 3D verification.');

    return [
      'error' => TRUE,
      'redirectUrl' => $response['redirect_url'],
    ];
  }

  /**
   * Checks whether cnc enabled or not on cart.
   *
   * @param array $data
   *   Cart data.
   *
   * @return bool
   *   CnC enabled or not for cart.
   */
  public function getCncStatusForCart(array $data) {
    static $cnc_enabled;
    if (isset($cnc_enabled)) {
      return $cnc_enabled;
    }

    // Whether CnC enabled or not.
    $cnc_enabled = TRUE;
    $cart_skus_list = [];
    foreach ($data['cart']['items'] as $item) {
      $cart_skus_list[] = $item['sku'];
    }

    if (!empty($cart_skus_list)) {
      $cart_skus_list = implode(',', $cart_skus_list);
      // Get CnC status.
      $cnc_enabled = $this->drupal->getCncStatusForCart($cart_skus_list);
    }

    return $cnc_enabled;
  }

  /**
   * Get cart from cache.
   *
   * @param bool $fetch_expired
   *   Whether we need stale data from cache or not.
   *
   * @return array
   *   Formatted cart data.
   */
  public function getCartFromCache(bool $fetch_expired = FALSE) {
    $expire = (int) $_ENV['CACHE_CART'];
    // If cart is available in cache, use that.
    if ($expire > 0 && ($cached_cart = $this->cache->get('cached_cart', $fetch_expired))) {
      return $this->formatCart($cached_cart);
    }
  }

  /**
   * Set cart in cache.
   *
   * @param array $cart
   *   Cart data.
   */
  public function setCartInCache(array $cart) {
    $expire = (int) $_ENV['CACHE_CART'];
    if ($expire > 0) {
      $this->cache->set('cached_cart', $expire, $cart);
    }
  }

  /**
   * Prepare message to log when API fail after payment successful.
   *
   * @param array $cart
   *   Cart Data.
   * @param array $data
   *   Payment data.
   * @param string $exception_message
   *   Exception message.
   * @param string $api
   *   API identifier which failed.
   * @param string $double_check_done
   *   Flag to say if double check was done or not.
   *
   * @return string
   *   Prepared error message.
   */
  private function prepareOrderFailedMessage(array $cart, array $data, string $exception_message, string $api, string $double_check_done) {
    $message[] = 'exception:' . $exception_message;
    $message[] = 'api:' . $api;
    $message[] = 'double_check_done:' . $double_check_done;
    $message[] = 'order_id:' . $cart['cart']['extension_attributes']['real_reserved_order_id'] ?? '';
    $message[] = 'cart_id:' . $cart['cart']['id'];
    $message[] = 'amount_paid:' . $cart['totals']['base_grand_total'];

    if ($this->settings->getSettings('place_order_debug_failure', 1)) {
      $payment_method = $data['paymentMethod']['method'] ?? $data['method'];
      $message[] = 'payment_method:' . $payment_method;
      if (isset($data['paymentMethod']['additional_data']) || isset($data['additional_data'])) {
        $additional_info = isset($data['paymentMethod']['additional_data'])
          ? $data['paymentMethod']['additional_data']
          : ($data['additional_data'] ?? NULL);
        $message[] = 'additional_information:' . json_encode($additional_info);
      }

      $message[] = 'shipping_method:' . $cart['shipping']['method'];
      foreach ($cart['shipping']['address']['custom_attributes'] as $shipping_attribute) {
        $message[] = $shipping_attribute['attribute_code'] . ':' . $shipping_attribute['value'];
      }
    }

    return implode('||', $message);
  }

  /**
   * Helper function to check if cart expired.
   *
   * @param array $cart
   *   Cart data.
   * @param array $checkout_settings
   *   Checkout settings.
   *
   * @return bool
   *   If cart is expired.
   */
  public function isCartTotalValid(array $cart, array $checkout_settings) {
    // Get cart totals.
    $cart_total = $cart['totals']['grand_total'];

    // Check if last update of our cart is more recent than X minutes.
    $expiration_time = $checkout_settings['purchase_expiration_time'];
    $cart_last_updated = isset($cart['cart']['updated_at']) ? $cart['cart']['updated_at'] : $cart['cart']['created_at'];
    $cart_last_updated_time = strtotime($cart_last_updated);
    $current_time = strtotime(date('Y-m-d H:i:s'));
    $time_difference = round(abs($current_time - $cart_last_updated_time) / 60, 2);

    // If time difference more then call getCart to get fresh data.
    if ($time_difference > $expiration_time) {
      try {
        $cart = $this->getCart();
      }
      catch (\Exception $e) {
        $this->logger->error('Error occurred while fetching cart information. Exception: @message', [
          '@message' => $e->getMessage(),
        ]);
      }
    }

    $fresh_cart_total = $cart['totals']['grand_total'];
    // If the totals differ then return false.
    if ($fresh_cart_total != $cart_total) {
      return FALSE;
    }

    return TRUE;
  }

}
