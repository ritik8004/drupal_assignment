<?php

namespace App\Service\CheckoutCom;

use App\Service\Magento\MagentoApiWrapper;

/**
 * Checkout.com Helper.
 *
 * @package App\Service\CheckoutCom
 */
class Helper {

  /**
   * Magento API Wrapper.
   *
   * @var \App\Service\Magento\MagentoApiWrapper
   */
  protected $magentoApi;

  /**
   * Checkout.com Helper constructor.
   *
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api
   *   Magento API Wrapper.
   */
  public function __construct(MagentoApiWrapper $magento_api) {
    $this->magentoApi = $magento_api;
  }

  /**
   * Get data from config for checkout.com.
   *
   * @param string|null $type
   *   Type of key, public_key or secret_key.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getConfig(?string $type) {
    static $config;

    if (empty($config)) {
      try {
        $config = $this->magentoApi->doRequest('GET', 'checkoutcom/getConfig');
      }
      catch (\Exception $e) {
        return NULL;
      }
    }

    return $type ? $config[$type] : $config;
  }

  /**
   * Get saved cards for of given customer.
   *
   * @param int $customer_id
   *   The customer id.
   *
   * @return array|mixed
   *   Return array of cards.
   */
  public function getCustomerCards(int $customer_id) {
    $url = sprintf('checkoutcom/getTokenList/?customer_id=%d', $customer_id);
    try {
      $card_list = $this->magentoApi->doRequest('GET', $url);
    }
    catch (\Exception $e) {
      return NULL;
    }

    return $card_list;
  }

}
