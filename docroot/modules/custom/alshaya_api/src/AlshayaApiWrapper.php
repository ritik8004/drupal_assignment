<?php

namespace Drupal\alshaya_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
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
   * Language code.
   *
   * @var string
   */
  protected $langcode;

  /**
   * Constructs a new AlshayaApiWrapper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   LoggerFactory object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, LoggerChannelFactoryInterface $logger_factory) {
    $this->config = $config_factory->get('alshaya_api.settings');
    $this->langcode = $language_manager->getCurrentLanguage()->getId();
    $this->logger = $logger_factory->get('alshaya_api');
  }

  /**
   * Function to override context langcode for API calls.
   *
   * @param string $langcode
   *   Language code to use for API calls.
   */
  public function updateStoreContext($langcode) {
    // Calling code will be responsible for doing all checks on the value.
    $this->langcode = $langcode;
  }

  /**
   * Function to get all the stores from the API.
   *
   * @param string $langcode
   *   Language code.
   *
   * @return mixed
   *   Stores array.
   */
  public function getStores($langcode) {
    $this->updateStoreContext($langcode);

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
   *
   * @return mixed
   *   Response from the API.
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
   * Function to get click and collect stores available nearby for a cart.
   *
   * @param int $cart_id
   *   The cart ID.
   * @param float $lat
   *   Latitude of user.
   * @param float $lon
   *   Longitude of user.
   *
   * @return mixed
   *   Response from the API.
   */
  public function getCartStores($cart_id, $lat = NULL, $lon = NULL) {
    if (\Drupal::state()->get('store_development_mode', 0) || empty($lat) || empty($long)) {
      $lat = 29;
      $lon = 48;
    }

    $endpoint = 'click-and-collect/stores/cart/' . $cart_id . '/lat/' . $lat . '/lon/' . $lon;
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
    $url = $this->config->get('magento_host');
    $url .= '/' . $this->config->get('magento_lang_prefix') . $this->langcode;
    $url .= '/' . $this->config->get('magento_api_base');
    $url .= '/' . $endpoint;

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
