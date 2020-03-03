<?php

namespace App\Service;

use App\Service\Magento\MagentoApiWrapper;
use App\Service\Magento\MagentoInfo;

/**
 * Class Orders.
 */
class Orders {

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
   * Get order by order id.
   *
   * @param int $order_id
   *   Order id.
   *
   * @return array
   *   Order data.
   */
  public function getOrder(int $order_id) {
    $url = sprintf('orders/%d', $order_id);

    try {
      $data = $this->magentoApiWrapper->doRequest('GET', $url);

      // @TODO: Do order processing.
      return $data;
    }
    catch (\Exception $e) {
      // Exception handling here.
      return $this->utility->getErrorResponse($e->getMessage(), $e->getCode());
    }
  }

}
