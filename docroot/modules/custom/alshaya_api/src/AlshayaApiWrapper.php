<?php

namespace Drupal\alshaya_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class AcqPromotionsManager.
 */
class AlshayaApiWrapper {

  /**
   * Stores the alshaya_api settings config array.
   *
   * @var array
   */
  protected $config;

  /**
   * Token to access APIs.
   *
   * @var string
   */
  protected $token;

  /**
   * Constructs a new AlshayaApiWrapper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->config = $config_factory->get('alshaya_api.settings');
    $this->logger = $logger_factory->get('alshaya_api');
  }

  /**
   * Function to get all the stores from the API.
   *
   * @return mixed
   *   Stores array.
   */
  public function getStores() {
    $endpoint = 'storeLocator/search?searchCriteria=';

    $response = $this->invokeApi($endpoint, [], 'GET');
    $stores = json_decode($response, TRUE);

    return $stores;
  }

  /**
   * Function to get click and collect stores available nearby for a product.
   *
   * @param string $sku
   *   String SKU.
   * @param float $lat
   *   Latitude of user.
   * @param float $lon
   *   Longitude of user.
   */
  public function getProductStores($sku, $lat, $lon) {
    if (\Drupal::state()->get('store_development_mode', 0)) {
      $lat = 29;
      $lon = 48;
    }

    $sku = urlencode($sku);

    $endpoint = 'click-and-collect/stores/product/' . $sku . '/lat/' . $lat . '/lon/' . $lon;
    $response = $this->invokeApi($endpoint, [], 'GET');
    $stores = json_decode($response, TRUE);
    return $stores;
  }

  /**
   * Function to invoke the API and get response.
   *
   * Note: GET parameters must be handled in invoking function itself.
   *
   * @param string $endpoint
   *   Endpoint URL, specific for the API call.
   * @param array $data
   *   Post data to send to API.
   * @param string $method
   *   GET or POST.
   * @param bool $requires_token
   *   Flag to specify if this API requires token or not.
   *
   * @return mixed
   *   Response from the API.
   */
  public function invokeApi($endpoint, array $data = [], $method = 'POST', $requires_token = TRUE) {
    $url = $this->config->get('magento_host') . '/' . $this->config->get('magento_api_base') . '/' . $endpoint;

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    if ($requires_token) {
      $token = $this->getToken();

      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
      ]);
    }

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->config->get('verify_ssl'));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->config->get('verify_ssl'));

    if ($method == 'POST') {
      curl_setopt($curl, CURLOPT_POST, TRUE);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

    $result = curl_exec($curl);
    curl_close($curl);

    return $result;
  }

  /**
   * Function to get token to access Magento APIs.
   *
   * @return string
   *   Token as string.
   */
  private function getToken() {
    if ($this->token) {
      return $this->token;
    }

    $cid = 'alshaya_api_token';

    if ($cache = \Drupal::cache('data')->get($cid)) {
      $this->token = $cache->data;
    }
    else {
      $endpoint = 'integration/admin/token';

      $data = [];
      $data['username'] = $this->config->get('username');
      $data['password'] = $this->config->get('password');

      $token = $this->invokeApi($endpoint, $data, 'POST', FALSE);

      $this->token = str_replace('"', '', $token);

      // Calculate the timestamp when we want the cache to expire.
      $expire = \Drupal::time()->getRequestTime() + $this->config->get('token_cache_time');

      // Set the stock in cache.
      \Drupal::cache('data')->set($cid, $this->token, $expire);
    }

    return $this->token;
  }

}
