<?php

namespace Drupal\alshaya_aura_react\Helper;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Helper class for Aura APIs.
 *
 * @package Drupal\alshaya_aura_react\Helper
 */
class AuraApiHelper {

  /**
   * Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Cache backend service for aura_api_config.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * AuraApiHelper constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend service for aura_api_config.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    LoggerChannelFactoryInterface $logger_factory,
    CacheBackendInterface $cache
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('alshaya_aura_react');
    $this->cache = $cache;
  }

  /**
   * Get Aura config from API.
   *
   * @return array
   *   Return array of config values.
   */
  public function getAuraApiConfig($configs = [], $reset = FALSE) {
    static $auraConfigs;

    if (!empty($auraConfigs)) {
      return $auraConfigs;
    }

    $auraApiConfig = !empty($configs)
      ? $configs
      : AuraDictionaryApiConstants::ALL_DICTONARY_API_CONSTANTS;

    foreach ($auraApiConfig as $value) {
      $cache_key = 'alshaya_aura_react:aura_api_configs:' . $value;

      if (!$reset && $cache = $this->cache->get($cache_key)) {
        $auraConfigs[$value] = $cache->data;
        continue;
      }

      $endpoint = 'customers/apcDicData/' . $value;
      $response = $this->apiWrapper->invokeApi($endpoint, [], 'GET');

      // @TODO: Remove hard coded response once API is funtional.
      $response = [
        "code" => "APC_CASHBACK_ACCRUAL_RATIO",
        "items" => [
          [
            "code" => "SAR",
            "order" => 1,
            "value" => "1",
          ],
        ],
      ];

      if (empty($response)) {
        $this->logger->error('No data found for api: @api.', [
          '@api' => $endpoint,
        ]);
      }

      $auraConfigs[$value] = $response;
      $this->cache->set($cache_key, $response, Cache::PERMANENT);
    }

    return $auraConfigs;
  }

  /**
   * Get Aura api config from cache.
   */
  public function getAuraApiConfigFromCache($key) {
    $cache_data = NULL;
    $cache_key = 'alshaya_aura_react:aura_api_configs:' . $key;
    $cache = $this->cache->get($cache_key);

    if (is_object($cache) && !empty($cache->data)) {
      $cache_data = $cache->data;
    }

    return $cache_data;
  }

}
