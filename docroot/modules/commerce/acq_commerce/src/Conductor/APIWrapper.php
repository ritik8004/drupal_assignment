<?php

namespace Drupal\acq_commerce\Conductor;

use Drupal\acq_commerce\I18nHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * APIWrapper class.
 */
class APIWrapper {

  use \Drupal\acq_commerce\Conductor\AgentRequestTrait;

  /**
   * Error code used internally for API Down cases.
   */
  const API_DOWN_ERROR_CODE = 600;

  /**
   * Store ID.
   *
   * @var mixed
   */
  protected $storeId;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * Constructor.
   *
   * @param ClientFactory $client_factory
   *   ClientFactory object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   LanguageManagerInterface object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   */
  public function __construct(ClientFactory $client_factory, ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, LoggerChannelFactory $logger_factory, I18nHelper $i18n_helper) {
    $this->clientFactory = $client_factory;
    $this->apiVersion = $config_factory->get('acq_commerce.conductor')->get('api_version');
    $this->logger = $logger_factory->get('acq_sku');
    $this->i18nHelper = $i18n_helper;

    // We always use the current language id to get store id. If required
    // function calling the api wrapper will pass different store id to
    // override this one.
    $this->storeId = $this->i18nHelper->getStoreIdFromLangcode($language_manager->getCurrentLanguage()->getId());
  }

  /**
   * Function to override context store id for API calls.
   *
   * @param mixed $store_id
   *   Store ID to use for API calls.
   */
  public function updateStoreContext($store_id) {
    // Calling code will be responsible for doing all checks on the value.
    $this->storeId = $store_id;
  }

  /**
   * Creates a new cart through the API.
   *
   * @param int $customer_id
   *   Optional customer ID to create the cart for.
   *
   * @return object
   *   Contains the new cart object.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function createCart($customer_id = NULL) {
    $endpoint = $this->apiVersion . '/agent/cart/create';

    $doReq = function ($client, $opt) use ($endpoint, $customer_id) {
      if (!empty($customer_id)) {
        $opt['form_params']['customer_id'] = $customer_id;
      }
      return ($client->post($endpoint, $opt));
    };

    $cart = [];

    try {
      $cart = $this->tryAgentRequest($doReq, 'createCart', 'cart');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $cart;
  }

  /**
   * Checks the stock for the given sku.
   *
   * @param string $sku
   *   The sku id.
   *
   * @return array|mixed
   *   Available stock detail.
   *
   * @throws \Exception
   */
  public function skuStockCheck($sku) {
    $endpoint = $this->apiVersion . "/agent/stock/$sku";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    try {
      return $this->tryAgentRequest($doReq, 'skuStockCheck', 'stock');
    }
    catch (ConductorException $e) {
      throw $e;
    }

    return NULL;
  }

  /**
   * Gets the user cart from a cart ID.
   *
   * @param int $cart_id
   *   Target cart ID.
   *
   * @return array
   *   Contains the retrieved cart array.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getCart($cart_id) {
    $endpoint = $this->apiVersion . "/agent/cart/$cart_id";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $cart = [];

    try {
      $cart = $this->tryAgentRequest($doReq, 'getCart', 'cart');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $cart;
  }

  /**
   * Update cart with the new cart array supplied.
   *
   * @param int $cart_id
   *   ID of cart to update.
   * @param object $cart
   *   Cart object to update with.
   *
   * @return array
   *   Full updated cart after submission.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function updateCart($cart_id, $cart) {
    $endpoint = $this->apiVersion . "/agent/cart/$cart_id";

    $doReq = function ($client, $opt) use ($endpoint, $cart) {
      $opt['json'] = $cart;

      return ($client->post($endpoint, $opt));
    };

    $cart = [];

    try {
      $cart = $this->tryAgentRequest($doReq, 'updateCart', 'cart');
      Cache::invalidateTags(['cart_' . $cart_id]);
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $cart;
  }

  /**
   * Associate a cart with a customer.
   *
   * @param int $cart_id
   *   ID of cart to associate.
   * @param int $customer_id
   *   ID of customer to associate with.
   *
   * @return bool
   *   A status of coupon being applied.
   *
   * @throws \Exception
   */
  public function associateCart($cart_id, $customer_id) {
    $endpoint = $this->apiVersion . "/agent/cart/$cart_id/associate";

    $doReq = function ($client, $opt) use ($endpoint, $customer_id, $cart_id) {
      $opt['json'] = [
        'customer_id' => $customer_id,
        'cart_id' => $cart_id,
      ];
      return ($client->post($endpoint, $opt));
    };

    $status = FALSE;

    try {
      $status = (bool) $this->tryAgentRequest($doReq, 'associateCart');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $status;
  }

  /**
   * Finalizes a cart's order.
   *
   * @param int $cart_id
   *   Cart ID to attempt placing an order for.
   *
   * @return array
   *   Result returned back from the conductor.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function placeOrder($cart_id) {
    $endpoint = $this->apiVersion . "/agent/cart/$cart_id/place";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->post($endpoint, $opt));
    };

    $result = [];

    try {
      $result = $this->tryAgentRequest($doReq, 'placeOrder');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $result;
  }

  /**
   * Gets shipping methods available on a order.
   *
   * @param int $cart_id
   *   Cart ID to retrieve shipping methods for.
   *
   * @return array
   *   If successful, returns a array of shipping methods.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getShippingMethods($cart_id) {
    $endpoint = $this->apiVersion . "/agent/cart/$cart_id/shipping";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $methods = [];

    try {
      $methods = $this->tryAgentRequest($doReq, 'getShippingMethods', 'methods');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $methods;
  }

  /**
   * Similar to getShippingMethods, retrieves methods with estimated costs.
   *
   * @param int $cart_id
   *   Cart ID to estimate for.
   * @param array|object $address
   *   Array with the target address.
   *
   * @return array
   *   Array of estimates and methods.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getShippingEstimates($cart_id, $address) {
    $endpoint = $this->apiVersion . "/agent/cart/$cart_id/estimate";

    $doReq = function ($client, $opt) use ($endpoint, $address) {
      $opt['json'] = $address;

      return ($client->post($endpoint, $opt));
    };

    $methods = [];

    try {
      $methods = $this->tryAgentRequest($doReq, 'getShippingEstimates', 'methods');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $methods;
  }

  /**
   * Gets the payment methods for the cart ID.
   *
   * @param int $cart_id
   *   Cart ID to get methods for.
   *
   * @return array
   *   Array of methods.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getPaymentMethods($cart_id) {
    $endpoint = $this->apiVersion . "/agent/cart/$cart_id/payments";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $methods = [];

    try {
      $methods = $this->tryAgentRequest($doReq, 'getPaymentMethods', 'methods');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $methods;
  }

  /**
   * Creates a customer by calling updateCustomer with NULL customer ID.
   *
   * @param string $first_name
   *   Customer first name.
   * @param string $last_name
   *   Customer last name.
   * @param string $email
   *   Customer e-mail.
   * @param string $password
   *   Optional password.
   *
   * @return array
   *   New customer array.
   */
  public function createCustomer($first_name, $last_name, $email, $password = NULL) {
    $customer = [];
    $customer['firstname'] = $first_name;
    $customer['lastname'] = $last_name;
    $customer['email'] = $email;

    // First check if the user exists in Magento.
    try {
      if ($existingCustomer = $this->getCustomer($email)) {
        $customer['customer_id'] = $existingCustomer['customer_id'];
      }
    }
    catch (\Exception $e) {
      // We are expecting error here for all emails that are not registered
      // already in magento.
      unset($customer['customer_id']);
    }

    return $this->updateCustomer($customer, $password);
  }

  /**
   * Updates a customer.
   *
   * @param array|object $customer
   *   Customer array to update (fully prepared array).
   * @param string $password
   *   Optional password.
   *
   * @return array
   *   New customer array.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function updateCustomer($customer, $password = NULL) {
    $endpoint = $this->apiVersion . "/agent/customer";

    $doReq = function ($client, $opt) use ($endpoint, $customer, $password) {

      $opt['json']['customer'] = $customer;

      if (!empty($password)) {
        $opt['json']['password'] = $password;
      }

      // Invoke the alter hook to allow all modules to update the customer data.
      \Drupal::moduleHandler()->alter('acq_commerce_update_customer_api_request', $opt);

      return ($client->post($endpoint, $opt));
    };

    $customer = [];

    try {
      $customer = $this->tryAgentRequest($doReq, 'updateCustomer', 'customer');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $customer;
  }

  /**
   * Authenticate customer.
   *
   * @param string $email
   *   Customer e-mail.
   * @param string $password
   *   Password.
   *
   * @return array
   *   New customer array.
   */
  public function authenticateCustomer($email, $password) {
    $endpoint = $this->apiVersion . "/agent/customer/" . $email;

    $doReq = function ($client, $opt) use ($endpoint, $password) {
      $opt['form_params']['password'] = $password;

      return ($client->post($endpoint, $opt));
    };

    $customer = [];

    try {
      $customer = $this->tryAgentRequest($doReq, 'authenticateCustomer', 'customer');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $customer;
  }

  /**
   * Gets customer by email.
   *
   * @param string $email
   *   Customer Email.
   *
   * @return array
   *   Customer array.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getCustomer($email) {
    $endpoint = $this->apiVersion . "/agent/customer/$email";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $customer = [];

    try {
      $customer = $this->tryAgentRequest($doReq, 'getCustomer', 'customer');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $customer;
  }

  /**
   * Gets customer orders by email.
   *
   * @param string $email
   *   Customer Email.
   *
   * @return array
   *   Orders array.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getCustomerOrders($email) {
    $endpoint = $this->apiVersion . "/agent/customer/orders/$email";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $orders = [];

    try {
      $orders = $this->tryAgentRequest($doReq, 'getCustomerOrders', 'orders');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $orders;
  }

  /**
   * Update order status and provide comment for update.
   *
   * @param int $order_id
   *   Order id.
   * @param string $status
   *   Order status.
   * @param string $comment
   *   Optional comment.
   *
   * @return bool|mixed
   *   Status of the update (TRUE/FALSE).
   *
   * @throws \Exception
   */
  public function updateOrderStatus($order_id, $status, $comment = '') {
    $endpoint = $this->apiVersion . '/agent/order/' . $order_id;

    $doReq = function ($client, $opt) use ($endpoint, $status, $comment) {
      $opt['json']['status'] = $status;
      $opt['json']['comment'] = $comment;

      return ($client->post($endpoint, $opt));
    };

    try {
      return $this->tryAgentRequest($doReq, 'updateOrderStatus', 'status');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return FALSE;
  }

  /**
   * Fetches product categories.
   *
   * @return array
   *   Array of product categories.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getCategories() {
    $endpoint = $this->apiVersion . "/agent/categories";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $categories = [];

    try {
      $categories = $this->tryAgentRequest($doReq, 'getCategories', 'categories');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $categories;
  }

  /**
   * Fetches product attribute options.
   *
   * @return array
   *   Array of product attribute options.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getProductOptions() {
    $endpoint = $this->apiVersion . "/agent/product/options";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $options = [];

    try {
      $options = $this->tryAgentRequest($doReq, 'getAttributeOptions', 'options');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $options;
  }

  /**
   * Fetches all promotions.
   *
   * @param string $type
   *   The type of promotion to retrieve from the API.
   *
   * @return array
   *   Array of promotions.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getPromotions($type = 'category') {
    // As the parameter is used in endpoint path, we restrict it to avoid
    // unexpected exception.
    if (!in_array($type, ['category', 'cart'])) {
      return [];
    }

    $endpoint = $this->apiVersion . "/agent/promotions/$type";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $result = [];

    try {
      $result = $this->tryAgentRequest($doReq, 'getPromotions', 'promotions');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $result;
  }

  /**
   * Gets products by updated time.
   *
   * @param \DateTime $date_time
   *   Datetime of the last update.
   *
   * @return array
   *   Array of products.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getProductsByUpdatedDate(\DateTime $date_time) {
    $endpoint = $this->apiVersion . "/agent/products";

    $doReq = function ($client, $opt) use ($endpoint, $date_time) {
      $opt['query']['updated'] = $date_time->format('Y-m-d H:i:s');
      return ($client->get($endpoint, $opt));
    };

    $categories = [];

    try {
      $categories = $this->tryAgentRequest($doReq, 'getProductsByUpdatedDates', 'products');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $categories;
  }

  /**
   * {@inheritdoc}
   */
  public function getProducts($count = 100) {
    $endpoint = $this->apiVersion . "/agent/products";
    $doReq = function ($client, $opt) use ($endpoint, $count) {
      $opt['query']['page_size'] = $count;

      // To allow hmac sign to be verified properly we need them in asc order.
      ksort($opt['query']);

      return ($client->get($endpoint, $opt));
    };
    $products = [];
    try {
      $products = $this->tryAgentRequest($doReq, 'getProducts', 'products');
    }
    catch (ConnectorException $e) {
      throw new RouteException(__FUNCTION__, $e->getMessage(), $e->getCode(), $this->getRouteEvents());
    }
    return $products;
  }

  /**
   * Invoke product full sync through ingest.
   *
   * Surrogate method for the ingest method. This is done to not have trait
   * conflicts.
   *
   * @param int $store_id
   *   Store id.
   * @param string $langcode
   *   Language code.
   * @param string $skus
   *   SKUs separated by comma.
   * @param string $category_id
   *   Category id.
   * @param int $page_size
   *   Page size.
   */
  public function productFullSync($store_id, $langcode, $skus = '', $category_id = '', $page_size = 0) {
    \Drupal::service('acq_commerce.ingest_api')->productFullSync($store_id, $langcode, $skus, $category_id, $page_size);
  }

  /**
   * Fetches a token for the requested payment method.
   *
   * @param string $method
   *   The ID of the requested payment token.
   *
   * @return string
   *   Payment token.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getPaymentToken($method) {
    $endpoint = $this->apiVersion . "/agent/cart/token/$method";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $result = [];

    try {
      $result = $this->tryAgentRequest($doReq, 'getPaymentToken', 'token');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $result;
  }

  /**
   * Function to subscribe an email for newsletter.
   *
   * @param string $email
   *   E-Mail to subscribe.
   */
  public function subscribeNewsletter($email) {
    $endpoint = $this->apiVersion . '/agent/newsletter/subscribe';

    $doReq = function ($client, $opt) use ($endpoint, $email) {
      $opt['form_params']['email'] = $email;

      return ($client->post($endpoint, $opt));
    };

    try {
      return $this->tryAgentRequest($doReq, 'subscribeNewsletter');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Preforms a test call to conductor.
   *
   * @return array
   *   Test request result.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function systemWatchdog() {
    $endpoint = $this->apiVersion . "/agent/system/wd";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $result = [];

    try {
      $result = $this->tryAgentRequest($doReq, 'systemWatchdog');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $result;
  }

  /**
   * Get linked skus for a given sku by linked type.
   *
   * @param string $sku
   *   The sku id.
   * @param string $type
   *   Linked type. Like - related/crosssell/upsell.
   *
   * @return array|mixed
   *   All linked skus of given type.
   *
   * @throws \Exception
   */
  public function getLinkedskus($sku, $type = LINKED_SKU_TYPE_ALL) {
    $endpoint = $this->apiVersion . "/agent/product/$sku/related/$type";

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    $result = [];

    try {
      $result = $this->tryAgentRequest($doReq, 'linkedSkus', 'related');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $result;
  }

    /**
     * Get position of the products in a category.
     *
     * @param int $category_id
     *   The category id.
     *
     * @return array
     *   All products with the positions in the given category.
     *
     * @throws \Exception
     */
    public function getProductPosition($category_id) {
        $endpoint = $this->apiVersion . "/agent/category/$category_id/position";

        $doReq = function ($client, $opt) use ($endpoint) {
            return ($client->get($endpoint, $opt));
        };

        $result = [];

        try {
            $result = $this->tryAgentRequest($doReq, 'productPosition', 'position');
        }
        catch (ConductorException $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        return $result;
    }

}
