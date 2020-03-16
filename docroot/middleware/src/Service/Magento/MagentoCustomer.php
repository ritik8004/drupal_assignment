<?php

namespace App\Service\Magento;

/**
 * Class MagentoCustomer.
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
   * Customer constructor.
   *
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API Wrapper.
   */
  public function __construct(MagentoInfo $magento_info,
                              MagentoApiWrapper $magento_api_wrapper) {
    $this->magentoInfo = $magento_info;
    $this->magentoApiWrapper = $magento_api_wrapper;
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
   *   Customer data.
   */
  public function createCustomer(string $email, string $firstname, string $lastname) {
    $url = 'customers';

    $data['customer'] = [
      'email' => $email,
      'firstname' => $firstname,
      'lastname' => $lastname,
      'store_id' => $this->magentoInfo->getMagentoStoreId(),
    ];

    return $this->magentoApiWrapper->doRequest('POST', $url, ['json' => (object) $data]);
  }

  /**
   * Get customer by email.
   *
   * @param string $email
   *   Email address.
   *
   * @return array|null
   *   Customer data if found.
   */
  public function getCustomerByMail(string $email) {
    $url = 'customers/search';
    $query['query'] = [
      'searchCriteria[filterGroups][0][filters][0][field]' => 'email',
      'searchCriteria[filterGroups][0][filters][0][value]' => $email,
      'searchCriteria[filterGroups][0][filters][0][condition_type]' => 'eq',
      'searchCriteria[filterGroups][1][filters][0][field]' => 'store_id',
      'searchCriteria[filterGroups][1][filters][0][value]' => $this->magentoInfo->getMagentoStoreId(),
      'searchCriteria[filterGroups][1][filters][0][condition_type]' => 'in',
    ];

    $result = $this->magentoApiWrapper->doRequest('GET', $url, $query);

    return empty($result['items']) ? [] : reset($result['items']);
  }

}
