<?php

namespace Drupal\alshaya_acm_checkoutcom\Helper;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Site\Settings;

/**
 * Api helper for checkout.com upapi.
 *
 * @package Drupal\alshaya_acm_checkoutcom\Helper
 */
class AlshayaAcmCheckoutComAPIHelper {

  /**
   * Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * AlshayaAcmCheckoutComAPIHelper constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   Logger factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache service.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactory $logger_factory,
    CacheBackendInterface $cache
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('alshaya_acm_checkoutcom');
    $this->cache = $cache;
  }

  /**
   * Get checkout.com upapi config.
   *
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getCheckoutcomUpApiConfig($reset = FALSE) {
    static $configs;

    if (!empty($configs)) {
      return $configs;
    }

    $cache_key = 'alshaya_acm_checkoutcom:api_configs';

    // Cache time in minutes, set 0 to disable caching.
    $cache_time = (int) Settings::get('checkout_com_upapi_config_cache_time', 5);

    // Disable caching if cache time set to 0 or null in settings.
    $reset = empty($cache_time) ? TRUE : $reset;

    $cache = $reset ? NULL : $this->cache->get($cache_key);
    if (is_object($cache) && !empty($cache->data)) {
      $configs = $cache->data;
    }
    else {
      $response = $this->apiWrapper->invokeApi(
        'checkoutcomupapi/config',
        [],
        'GET'
      );

      $configs = Json::decode($response);

      if (empty($configs)) {
        $this->logger->error('Invalid response from checkout.com upapi api, @response', [
          '@response' => Json::encode($configs),
        ]);
      }
      elseif (!empty($configs) && $cache_time > 0) {
        // Cache only if enabled (cache_time set).
        $this->cache->set($cache_key, $configs, strtotime("+${cache_time} minutes"));
      }
    }

    // Try resetting once.
    if (empty($configs) && !($reset)) {
      return $this->getCheckoutcomUpApiConfig(TRUE);
    }

    return $configs;
  }

  /**
   * Get saved cards for checkout.com upapi method.
   *
   * @param int $customer_id
   *   Customer id.
   *
   * @return array
   *   Saved cards array if available.
   */
  public function getSavedCards(int $customer_id) {
    static $static = [];

    if (isset($static[$customer_id])) {
      return $static[$customer_id];
    }

    $saved_cards = [];

    $endpoint = 'checkoutcomupapi/getTokenList';
    $data = [
      'customer_id' => $customer_id,
    ];

    $allowed_cards_mapping = Settings::get('checkout_com_upapi_accepted_cards_mapping', []);

    $response = $this->apiWrapper->invokeApi($endpoint, $data, 'GET');
    $response = is_string($response) ? Json::decode($response) : $response;

    $items = $response['items'] ?? [];
    uasort($items, function ($a, $b) {
      return (strtotime($a['created_at']) > strtotime($b['created_at'])) ? -1 : 1;
    });

    foreach ($items as $item) {
      $saved_card = Json::decode($item['token_details']);
      $saved_card['public_hash'] = base64_encode($item['public_hash']);

      // Mape the card type to card type machine name.
      $type = strtolower($saved_card['type']);
      $saved_card['type'] = $allowed_cards_mapping[$type] ?? $saved_card['type'];

      $saved_cards[$saved_card['public_hash']] = $saved_card;
    }

    $static[$customer_id] = $saved_cards;
    return $saved_cards;
  }

}
