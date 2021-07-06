<?php

namespace App\Service\Magento;

use App\Service\Utility;

/**
 * Provides helper functions to get and create cusomer info.
 *
 * @package App\Service\Magento
 */
class MagentoCustomer {

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
   * Customer constructor.
   *
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API Wrapper.
   * @param \App\Service\Utility $utility
   *   Utility service.
   */
  public function __construct(MagentoInfo $magento_info,
                              MagentoApiWrapper $magento_api_wrapper,
                              Utility $utility) {
    $this->magentoInfo = $magento_info;
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->utility = $utility;
  }

  /**
   * Create customer in magento.
   *
   * @param string $email
   *   E-Mail address.
   * @param string $firstname
   *   First name.
   * @param string $lastname
   *   Last name.
   *
   * @return array
   *   Customer data if API call is successful else an array containing the
   *   error message.
   */
  public function createCustomer(string $email, string $firstname, string $lastname) {
    $url = 'customers';

    $data['customer'] = [
      'email' => $email,
      'firstname' => $firstname,
      'lastname' => $lastname,
      'store_id' => $this->magentoInfo->getMagentoStoreId(),
    ];

    $request_options = [
      'timeout' => $this->magentoInfo->getPhpTimeout('customer_create'),
      'json' => (object) $data,
    ];

    try {
      return $this->magentoApiWrapper->doRequest('POST', $url, $request_options);
    }
    catch (\Exception $e) {
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Get customer by email.
   *
   * @param string $email
   *   Email address.
   *
   * @return array|null
   *   Customer data if API call is successful else and array containing the
   *   error message.
   */
  public function getCustomerByMail(string $email) {
    $url = 'customers/search';

    $request_options = [
      'timeout' => $this->magentoInfo->getPhpTimeout('customer_search'),
      'query' => [
        'searchCriteria[filterGroups][0][filters][0][field]' => 'email',
        'searchCriteria[filterGroups][0][filters][0][value]' => $email,
        'searchCriteria[filterGroups][0][filters][0][condition_type]' => 'eq',
        'searchCriteria[filterGroups][1][filters][0][field]' => 'store_id',
        'searchCriteria[filterGroups][1][filters][0][value]' => implode(
          ',', array_values($this->magentoInfo->getMagentoStoreIds())
        ),
        'searchCriteria[filterGroups][1][filters][0][condition_type]' => 'in',
      ],
    ];

    try {
      $result = $this->magentoApiWrapper->doRequest('GET', $url, $request_options);
    }
    catch (\Exception $e) {
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }

    return empty($result['items']) ? [] : reset($result['items']);
  }

  /**
   * Get customer by customer id.
   *
   * @param string $customer_id
   *   Customer Id.
   *
   * @return array|null
   *   Customer data if API call is successful else an array containing the
   *   error message.
   */
  public function getCustomerById(string $customer_id) {
    $url = sprintf('customers/%d', $customer_id);

    $request_options = [
      'timeout' => $this->magentoInfo->getPhpTimeout('customer_details'),
    ];

    try {
      $result = $this->magentoApiWrapper->doRequest('GET', $url, $request_options);
    }
    catch (\Exception $e) {
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }

    return $result;
  }

}
