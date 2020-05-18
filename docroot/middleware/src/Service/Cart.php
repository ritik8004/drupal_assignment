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
use Drupal\alshaya_spc\Helper\SecureText;
use Psr\Log\LoggerInterface;

/**
 * Class Cart.
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
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

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
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
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
    LoggerInterface $logger
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
    $this->logger = $logger;
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
   * Get cart by cart id.
   *
   * @param bool $force
   *   True to load data from api, false from cache.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getCart($force = FALSE) {
    if (!empty(static::$cart) && !$force) {
      return static::$cart;
    }

    $cart_id = $this->getCartId();
    if (empty($cart_id)) {
      return NULL;
    }

    $url = sprintf('carts/%d/getCart', $cart_id);

    try {
      static::$cart = $this->magentoApiWrapper->doRequest('GET', $url);
      static::$cart = $this->formatCart(static::$cart);
      return static::$cart;
    }
    catch (\Exception $e) {
      static::$cart = NULL;

      if (strpos($e->getMessage(), 'No such entity with cartId') > -1) {
        $this->session->updateDataInSession(self::SESSION_STORAGE_KEY, NULL);
      }

      $this->logger->error('Error while getting cart from MDC. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
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
      $this->session->updateDataInSession(self::SESSION_STORAGE_KEY, $cart_id);
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
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function addUpdateRemoveItem(string $sku, ?int $quantity, string $action, array $options = []) {
    $option_data = [];
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
      || empty($cart['cart']['billing_address']['firstname'])
      || $cart['cart']['billing_address']['city'] == 'NONE') {
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

    $data['billing'] = $billing_data;

    return $this->updateCart($data);
  }

  /**
   * Adds a customer to cart.
   *
   * @param int $customer_id
   *   Customer id.
   *
   * @return mixed
   *   Response.
   */
  public function associateCartToCustomer(int $customer_id) {
    $cart_id = $this->getCartId();
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
        $this->getCart();
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
    $extension['action'] = 'update payment';

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

    return $this->updateCart($update);
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
          $this->paymentData->setPaymentData($this->getCartId(), $response['id'], $response['data']);
          throw new \Exception($response['redirectUrl'], 302);
        }

        throw new \Exception('Failed to initiate K-Net request.', 500);

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
            // We will use this again to redirect back to Drupal.
            $response['langcode'] = $this->settings->getRequestLanguage();
            $this->paymentData->setPaymentData($this->getCartId(), $response['id'], $response);
            throw new \Exception($response[APIWrapper::REDIRECT_URL], 302);
          }

          throw new \Exception('Failed to initiate 3D request.', 500);
        }
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

    try {
      static::$cart = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => (object) $data]);
      static::$cart = $this->formatCart(static::$cart);
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

      return $cart;
    }
    catch (\Exception $e) {
      static::$cart = NULL;

      // Re-set cart id in session if exception is for cart not found.
      if (strpos($e->getMessage(), 'No such entity with cartId') > -1) {
        $this->session->updateDataInSession(self::SESSION_STORAGE_KEY, NULL);
      }
      else {
        $this->cancelCartReservation($e->getMessage());
      }

      // Check the exception type from drupal.
      $exception_type = $this->exceptionType($e->getMessage());
      // We want to throw error for add to cart action, to display errors
      // if api fails. (We don't need to return cart object as we only care
      // about error whenever we are on pdp / Add to cart form.)
      // Because, If we return cart object, it won't show any error as we are
      // not passing error with cart object, and with successful cart object it
      // will show notification of add to cart (Which we don't need here.).
      $is_add_to_Cart = (!empty($data['extension'])
        && $data['extension']->action == CartActions::CART_ADD_ITEM);
      // If exception type is of stock limit or of quantity limit,
      // refresh the stock for the sku items in cart from MDC to drupal.
      if (!empty($exception_type) && !$is_add_to_Cart) {
        // Get cart object if already not available.
        $cart = !empty($cart) ? $cart : $this->getCart();
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

      $this->logger->error('Error while updating cart on MDC. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);
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

    $key = md5(json_encode($data));
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

    $expire = (int) $_ENV['CACHE_TIME_LIMIT_PAYMENT_METHODS'];

    $static[$key] = $expire > 0 ? $this->cache->get($key) : NULL;
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

    if ($expire > 0) {
      $this->cache->set($key, $expire, $static[$key]);
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

    try {
      $cart = $this->getCart();
      $email = $this->getCartCustomerEmail();

      $result = $this->magentoApiWrapper->doRequest('PUT', $url, ['json' => $data]);
      $order_id = (int) str_replace('"', '', $result);

      // Remove cart id and other caches from session.
      $this->session->updateDataInSession(self::SESSION_STORAGE_KEY, NULL);
      $this->cache->delete('delivery_methods');
      $this->cache->delete('payment_methods_home_delivery');
      $this->cache->delete('payment_methods_click_and_collect');
      $this->cache->delete('payment_method');

      // Set order in session for later use.
      $this->session->updateDataInSession(Orders::SESSION_STORAGE_KEY, $order_id);

      // Post order id and cart data to Drupal.
      $data = [
        'order_id' => (int) $order_id,
        'cart' => $cart['cart'],
        'payment_method' => $data['paymentMethod']['method'],
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
    catch (\Exception $e) {
      $this->cancelCartReservation($e->getMessage());
      $this->logger->error('Error while placing order. Error message: @message', [
        '@message' => $e->getMessage(),
      ]);
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
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

    return $cart;
  }

}
