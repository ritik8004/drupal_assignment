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
    $customer_array = [
      'customer_id' => NULL,
      'firstname' => $first_name,
      'lastname' => $last_name,
      'email' => $email,
    ];

    return $this->apiWrapper->updateCustomer($customer_array, $password);
  }

}
