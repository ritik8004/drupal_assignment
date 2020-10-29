<?php

namespace App\Service\CheckoutCom;

use App\Service\Config\SystemSettings;
use App\Service\Magento\MagentoApiWrapper;

/**
 * Helper class for checkout_com paymetn method.
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
   * Magento API Wrapper.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

  /**
   * Checkout.com Helper constructor.
   *
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api
   *   Magento API Wrapper.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   */
  public function __construct(
    MagentoApiWrapper $magento_api,
    SystemSettings $settings
  ) {
    $this->magentoApi = $magento_api;
    $this->settings = $settings;
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
      $request_options = [
        'timeout' => $this->magentoApi->getMagentoInfo()->getPhpTimeout('checkoutcom_config_get'),
      ];
      try {
        $config = $this->magentoApi->doRequest('GET', 'checkoutcom/getConfig', $request_options);
      }
      catch (\Exception $e) {
        return NULL;
      }
    }

    return $type ? $config[$type] : $config;
  }

  /**
   * Get data from config for checkout.com upapi.
   *
   * @return array
   *   Return array of keys.
   */
  public function getCheckoutComUpapiConfig() {
    static $config;

    if (empty($config)) {
      try {
        $config = $this->magentoApi->doRequest('GET', 'checkoutcomupapi/config');
      }
      catch (\Exception $e) {
        return NULL;
      }
    }

    return $config;
  }

  /**
   * Get saved cards for of given customer.
   *
   * @param string $method
   *   Payment method.
   * @param int $customer_id
   *   The customer id.
   *
   * @return array|mixed
   *   Return array of cards.
   */
  public function getCustomerCards(string $method, int $customer_id) {
    $request_options = [];

    switch ($method) {
      case 'checkout_com':
        $endpoint = 'checkoutcom/getTokenList';
        $request_options = [
          'timeout' => $this->magentoApi->getMagentoInfo()->getPhpTimeout('checkoutcom_token_list'),
        ];
        break;

      case 'checkout_com_upapi':
        $endpoint = 'checkoutcomupapi/getTokenList';
        break;

      default:
        return NULL;
    }

    $url = sprintf($endpoint . '?customer_id=%d', $customer_id);
    try {
      $card_list = $this->magentoApi->doRequest('GET', $url, $request_options);
    }
    catch (\Exception $e) {
      return NULL;
    }

    return $card_list;
  }

}
