<?php

namespace App\Service;

use App\Service\Magento\MagentoApiWrapper;
use App\Service\Magento\MagentoInfo;
use Drupal\alshaya_acm_customer\HelperTrait\Orders as OrdersHelper;

/**
 * Provides details about orders made in the site.
 */
class Orders {

  use OrdersHelper;

  /**
   * The last order id storage key.
   */
  const SESSION_STORAGE_KEY = 'last_order';

  /**
   * The cart id of the order placed.
   */
  const ORDER_CART_ID = 'order_cart_id';

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
   * Orders constructor.
   *
   * @param \App\Service\Magento\MagentoInfo $magento_info
   *   Magento info service.
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API Wrapper.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(MagentoInfo $magento_info,
                              MagentoApiWrapper $magento_api_wrapper,
                              Utility $utility) {
    $this->magentoInfo = $magento_info;
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->utility = $utility;
  }

  /**
   * Helper function to get last order of the customer.
   *
   * @param int $customer_id
   *   Customer ID to get order for.
   *
   * @return array
   *   Order array if found.
   */
  public function getLastOrder(int $customer_id) {
    $query = $this->getOrdersQuery('customer_id', $customer_id);
    $query['searchCriteria']['pageSize'] = 1;

    $requestOptions = [
      'query' => $query,
      'timeout' => $this->magentoInfo->getPhpTimeout('order_search'),
    ];
    $result = $this->magentoApiWrapper->doRequest('GET', 'orders', $requestOptions);
    $count = $result['total_count'] ?? 0;
    if (empty($count)) {
      return NULL;
    }

    $order = reset($result['items']);
    return $this->cleanupOrder($order);
  }

  /**
   * Get order from magento by order id.
   *
   * @param int $order_id
   *   Order id.
   *
   * @return array|bool
   *   Order array or false if not found.
   */
  public function getOrderById(int $order_id) {
    $url = $url = sprintf('orders/%d', $order_id);
    try {
      $result = $this->magentoApiWrapper->doRequest('GET', $url);
      return $result;
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return FALSE;
  }

}
