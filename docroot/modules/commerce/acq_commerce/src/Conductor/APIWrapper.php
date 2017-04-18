<?php
/**
 * @file
 * Contains Drupal\acq_commerce\Conductor\APIWrapper.
 */

namespace Drupal\acq_commerce\Conductor;

class APIWrapper {

  use \Drupal\acq_commerce\Conductor\AgentRequestTrait;

  /**
   * Constructor
   *
   * @param ClientFactory $client
   *
   * @return void
   */
  public function __construct(ClientFactory $client_factory) {
    $this->clientFactory = $client_factory;
  }

  /**
   * Creates a new cart through the API.
   *
   * @param $customer_id - Optional customer ID to create the cart for.
   * @return array - Contains the new cart array.
   */
  public function createCart($customer_id = NULL) {
    $endpoint = 'cart/create';

    $doReq = function($client, $opt) use ($endpoint, $customer_id) {
      if (!empty($customer_id)) {
        $opt['form_params']['customer_id'] = $customer_id;
      }
      return($client->post($endpoint, $opt));
    };

    $cart = array();

    try {
      $cart = $this->tryAgentRequest($doReq, 'createCart', 'cart');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $cart;
  }

  /**
   * Gets the user cart from a cart ID.
   *
   * @param $cart_id - Target cart ID.
   * @return array - Contains the retrieved cart array.
   */
  public function getCart($cart_id) {
    $endpoint = "cart/$cart_id";

    $doReq = function($client, $opt) use ($endpoint) {
      return($client->get($endpoint, $opt));
    };

    $cart = array();

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
   * @param $cart - Cart array to update with.
   * @return array - Full updated cart after submission.
   */
  public function updateCart($cart_id, $cart) {
    $endpoint = "cart/$cart_id";

    $doReq = function($client, $opt) use ($endpoint, $cart) {
      $opt['json'] = $cart;

      return($client->post($endpoint, $opt));
    };

    $cart = array();

    try {
      $cart = $this->tryAgentRequest($doReq, 'updateCart', 'cart');
    }
    catch (ConductorResultException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $cart;
  }

  /**
   * Finalizes a cart's order.
   *
   * @param $cart_id - Cart ID to attempt placing an order for.
   * @return array - Result returned back from the conductor.
   */
  public function placeOrder($cart_id) {
    $endpoint = "cart/$cart_id/place";

    $doReq = function($client, $opt) use ($endpoint) {
      return($client->post($endpoint, $opt));
    };

    $result = array();

    try {
      $result = $this->tryAgentRequest($doReq, 'placeOrder', 'cart');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $result;
  }

  /**
   * Gets shipping methods available on a order.
   *
   * @param $cart_id - Cart ID to retrieve shipping methods for.
   * @return array - If successful, returns a array of shipping methods.
   */
  public function getShippingMethods($cart_id) {
    $endpoint = "cart/$cart_id/shipping";

    $doReq = function($client, $opt) use ($endpoint) {
      return($client->get($endpoint, $opt));
    };

    $methods = array();

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
   * @param $cart_id - Cart ID to estimate for.
   * @param $address - Array with the target address.
   * @return array - Array of estimates and methods.
   */
  public function getShippingEstimates($cart_id, $address) {
    $endpoint = "cart/$cart_id/estimate";

    $doReq = function($client, $opt) use ($endpoint, $address) {
      $opt['json'] = $address;

      return($client->post($endpoint, $opt));
    };

    $methods = array();

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
   * @param $cart_id - Cart ID to get methods for.
   * @return array - Array of methods.
   */
  public function getPaymentMethods($cart_id) {
    $endpoint = "cart/$cart_id/payments";

    $doReq = function($client, $opt) use ($endpoint) {
      return($client->get($endpoint, $opt));
    };

    $methods = array();

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
   * @param $first_name - Customer first name.
   * @param $last_name - Customer last name.
   * @param $email - Customer e-mail.
   * @return array - New customer array.
   */
  public function createCustomer($first_name, $last_name, $email) {
    // First check if the user exists in Magento.
    try {
      if ($existingCustomer = $this->getCustomer($email)) {
        return $this->updateCustomer($existingCustomer['customer_id'], $first_name, $last_name, $email);
      }
    }
    catch (\Exception $e) {
      // We are expecting error here for all emails that are not registered
      // already in magento.
    }

    return $this->updateCustomer(NULL, $first_name, $last_name, $email);
  }

  /**
   * Updates a customer by customer ID.
   *
   * @param $customer_id - Customer ID to update.
   * @param $first_name - Customer first name.
   * @param $last_name - Customer last name.
   * @param $email - Customer e-mail.
   * @return array - New customer array.
   */
  public function updateCustomer($customer_id, $first_name, $last_name, $email) {
    $endpoint = "customer";

    $doReq = function($client, $opt) use ($endpoint, $customer_id, $first_name, $last_name, $email) {
      if (!empty($customer_id)) {
        $opt['form_params']['customer[customer_id]'] = $customer_id;
      }

      $opt['form_params']['customer[firstname]'] = $first_name;
      $opt['form_params']['customer[lastname]'] = $last_name;
      $opt['form_params']['customer[email]'] = $email;

      return($client->post($endpoint, $opt));
    };

    $customer = array();

    try {
      $customer = $this->tryAgentRequest($doReq, 'updateCustomer', 'customer');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $customer;
  }

  /**
   * Gets customer by email.
   *
   * @param $email - Customer Email.
   * @return array - Customer array.
   */
  public function getCustomer($email) {
    $endpoint = "customer/$email";

    $doReq = function($client, $opt) use ($endpoint) {
      return($client->get($endpoint, $opt));
    };

    $customer = array();

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
   * @param $email - Customer Email.
   * @return array - Orders array.
   */
  public function getCustomerOrders($email) {
    $endpoint = "customer/orders/$email";

    $doReq = function($client, $opt) use ($endpoint) {
      return($client->get($endpoint, $opt));
    };

    $orders = array();

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
   * @return array - Array of product categories.
   */
  public function getCategories() {
    $endpoint = "categories";

    $doReq = function($client, $opt) use ($endpoint) {
      return($client->get($endpoint, $opt));
    };

    $categories = array();

    try {
      $categories = $this->tryAgentRequest($doReq, 'getCategories', 'products');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $categories;
  }

  /**
   * Gets products by updated time.
   *
   * @param \DateTime $date_time
   * @return array - Array of products.
   */
  public function getProductsByUpdatedDate(\DateTime $date_time) {
    $endpoint = "products";

    $doReq = function($client, $opt) use ($endpoint, $date_time) {
      $opt['query']['updated'] = $date_time->format('Y-m-d H:i:s');
      return($client->get($endpoint, $opt));
    };

    $categories = array();

    try {
      $categories = $this->tryAgentRequest($doReq, 'getProductsByUpdatedDates', 'products');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $categories;
  }

  /**
   * Surrogate method for the ingest method. This is done to not have trait
   * conflicts.
   */
  public function productFullSync() {
    \Drupal::service('acq_commerce.ingest_api')->productFullSync();
  }

  /**
   * Fetches a token for the requested payment method.
   *
   * @param string $method - The ID of the requested payment token.
   * @return string - Token.
   */
  public function getPaymentToken($method) {
    $endpoint = "cart/token/$method";

    $doReq = function($client, $opt) use ($endpoint) {
      return($client->get($endpoint, $opt));
    };

    $result = array();

    try {
      $result = $this->tryAgentRequest($doReq, 'getPaymentToken', 'token');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $result;
  }

  /**
 * Preforms a test call to conductor.
 *
 * @return array - Test request result.
 */
  public function systemWatchdog() {
    $endpoint = "system/wd";

    $doReq = function($client, $opt) use ($endpoint) {
      return($client->get($endpoint, $opt));
    };

    $result = array();

    try {
      $result = $this->tryAgentRequest($doReq, 'systemWatchdog', 'system');
    }
    catch (ConductorException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }

    return $result;
  }
}
