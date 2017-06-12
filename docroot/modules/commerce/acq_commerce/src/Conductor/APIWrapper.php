<?php

namespace Drupal\acq_commerce\Conductor;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * APIWrapper class.
 */
class APIWrapper {

  use \Drupal\acq_commerce\Conductor\AgentRequestTrait;

  /**
   * Constructor.
   *
   * @param ClientFactory $client_factory
   *   ClientFactory object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(ClientFactory $client_factory, ConfigFactoryInterface $config_factory, LoggerChannelFactory $logger_factory) {
    $this->clientFactory = $client_factory;
    $this->apiVersion = $config_factory->get('acq_commerce.conductor')->get('api_version');
    $this->logger = $logger_factory->get('acq_sku');
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

    $stock = [];

    try {
      // Cache id.
      $cid = 'stock:' . $sku;

      // If information is cached.
      if ($cache = \Drupal::cache('data')->get($cid)) {
        $stock = $cache->data;
      }
      else {
        $stock = $this->tryAgentRequest($doReq, 'skuStockCheck', 'stock');
        $stock_check_proportion = \Drupal::config('acq_commerce.conductor')->get('stock_check_cache_proportion');

        // Calculate the time in seconds (config contains in minutes).
        $time = $stock['quantity'] ? $stock['quantity'] * $stock_check_proportion * 60 : $stock_check_proportion * 60;

        // Calculate the timestamp when we want the cache to expire.
        $expire = \Drupal::time()->getRequestTime() + $time;

        // Set the stock in cache.
        \Drupal::cache('data')->set($cid, $stock, $expire);
      }
    }
    catch (ConductorException $e) {
      // Log the stock error, do not throw error if stock info is missing.
      $this->logger->emergency('Unable to get the stock for @sku : @message', ['@sku' => $sku, '@message' => $e->getMessage()]);
    }

    return $stock;
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
   * @param $customer_id
   *   ID of customer to associate with.
   *
   * @return bool
   *   A status of coupon being applied.
   * @throws \Exception
   */
  public function associateCart($cart_id, $customer_id) {
    $endpoint = $this->apiVersion . "/agent/cart/$cart_id/associate";

    $doReq = function ($client, $opt) use ($endpoint, $customer_id, $cart_id) {
      $opt['json'] = [
        'customer_id' => $customer_id,
        'cart_id' => $cart_id
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
    // First check if the user exists in Magento.
    try {
      if ($existingCustomer = $this->getCustomer($email)) {
        return $this->updateCustomer($existingCustomer['customer_id'], $first_name, $last_name, $email, $password);
      }
    }
    catch (\Exception $e) {
      // We are expecting error here for all emails that are not registered
      // already in magento.
    }

    return $this->updateCustomer(NULL, $first_name, $last_name, $email, $password);
  }

  /**
   * Updates a customer by customer ID.
   *
   * @param int $customer_id
   *   Customer ID to update.
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
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function updateCustomer($customer_id, $first_name, $last_name, $email, $password = NULL) {
    $endpoint = $this->apiVersion . "/agent/customer";

    $doReq = function ($client, $opt) use ($endpoint, $customer_id, $first_name, $last_name, $email, $password) {
      if (!empty($customer_id)) {
        $opt['form_params']['customer[customer_id]'] = $customer_id;
      }

      $opt['form_params']['customer[firstname]'] = $first_name;
      $opt['form_params']['customer[lastname]'] = $last_name;
      $opt['form_params']['customer[email]'] = $email;

      if (!empty($password)) {
        $opt['form_params']['password'] = $password;
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
   * @return array
   *   Array of promotions.
   */
  public function getPromotions() {
    $endpoint = $this->apiVersion . "/agent/promotions/category";

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
   * Invoke product full sync through ingest.
   *
   * Surrogate method for the ingest method. This is done to not have trait
   * conflicts.
   */
  public function productFullSync() {
    \Drupal::service('acq_commerce.ingest_api')->productFullSync();
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
    $endpoint = $this->apiVersion . "/agent/newsletter/subscribe";

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
      $result = $this->tryAgentRequest($doReq, 'systemWatchdog', 'system');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $result;
  }

}
