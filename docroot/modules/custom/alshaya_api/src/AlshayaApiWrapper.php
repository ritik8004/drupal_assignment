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
   *
   * @return mixed
   *   Response from the API.
   */
  public function invokeApi($endpoint, array $data = [], $method = 'POST') {
    $url = $this->config->get('magento_host') . '/' . $this->config->get('magento_api_base') . '/' . $endpoint;

    $oauth_data = [
      'oauth_consumer_key' => $this->config->get('consumer_key'),
      'oauth_nonce' => md5(uniqid(rand(), TRUE)),
      'oauth_signature_method' => 'HMAC-SHA1',
      'oauth_timestamp' => time(),
      'oauth_token' => $this->config->get('access_token'),
      'oauth_version' => '1.0',
    ];

    $oauth_data['oauth_signature'] = self::sign($method, $url, $oauth_data, $this->config->get('consumer_secret'), $this->config->get('access_token_secret'));

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
      'Authorization: OAuth ' . http_build_query($oauth_data, '', ','),
    ]);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

    if ($method == 'POST') {
      curl_setopt($curl, CURLOPT_POST, TRUE);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

    $result = curl_exec($curl);
    curl_close($curl);

    return $result;
  }

  /**
   * Helper function to get oauth signature for data.
   *
   * @param string $method
   *   Method - GET or POST.
   * @param string $url
   *   URL of the API.
   * @param array $data
   *   Oauth data.
   * @param string $consumerSecret
   *   Consumer secret.
   * @param string $tokenSecret
   *   Token secret.
   *
   * @return string
   *   Signed value.
   */
  protected static function sign($method, $url, array $data, $consumerSecret, $tokenSecret) {
    $url = urlEncodeAsZend($url);
    $data = urlEncodeAsZend(http_build_query($data, '', '&'));
    $data = implode('&', [$method, $url, $data]);
    $secret = implode('&', [$consumerSecret, $tokenSecret]);
    return base64_encode(hash_hmac('sha1', $data, $secret, TRUE));
  }

  /**
   * Helper function to encode URL as Zend.
   *
   * @param string $value
   *   Value to encode.
   *
   * @return mixed|string
   *   Encoded value.
   */
  protected static function urlEncodeAsZend($value) {
    $encoded = rawurlencode($value);
    $encoded = str_replace('%7E', '~', $encoded);
    return $encoded;
  }

}
