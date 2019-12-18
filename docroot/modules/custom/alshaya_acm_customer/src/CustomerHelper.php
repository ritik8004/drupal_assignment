<?php

namespace Drupal\alshaya_acm_customer;

use Drupal\acq_commerce\Conductor\APIWrapper;

/**
 * Class CustomerHelper.
 *
 * @package Drupal\alshaya_acm_customer
 */
class CustomerHelper {

  /**
   * API Wrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * CustomerHelper constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   */
  public function __construct(APIWrapper $api_wrapper) {
    $this->apiWrapper = $api_wrapper;
  }

  /**
   * Create customer.
   *
   * @param string $email
   *   E-Mail.
   * @param string $first_name
   *   First name.
   * @param string $last_name
   *   Last name.
   * @param string $password
   *   Password in plain text format.
   *
   * @return array
   *   Customer array.
   */
  public function createCustomer($email, $first_name, $last_name, $password) {
    return $this->updateCustomer(NULL, $email, $first_name, $last_name, $password);
  }

  /**
   * Update customer.
   *
   * @param mixed $customer_id
   *   Customer ID.
   * @param string $email
   *   E-Mail.
   * @param string $first_name
   *   First name.
   * @param string $last_name
   *   Last name.
   * @param string $password
   *   Password in plain text format.
   *
   * @return array
   *   Customer array.
   */
  public function updateCustomer($customer_id, $email, $first_name, $last_name, $password) {
    $customer_array = [
      'customer_id' => $customer_id,
      'firstname' => $first_name,
      'lastname' => $last_name,
      'email' => $email,
    ];

    return $this->apiWrapper->updateCustomer($customer_array, [
      'password' => $password,
    ]);
  }

  /**
   * Get existing user or create user if it does not exists.
   *
   * @param string $email
   *   E-Mail.
   * @param string $first_name
   *   First name.
   * @param string $last_name
   *   Last name.
   * @param string $password
   *   Password in plain text format.
   *
   * @return array|mixed
   *   Return customer array or throw exceptions.
   */
  public function getCustomer($email, $first_name, $last_name, $password) {
    $customer = $this->apiWrapper->getCustomer($email, NULL);
    if (!empty($customer)) {
      return $customer;
    }
    return $this->updateCustomer(NULL, $email, $first_name, $last_name, $password);
  }

}
