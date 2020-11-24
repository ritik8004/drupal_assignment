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
  public function getAuraApiConfig() {
    static $auraConfigs;

    if (!empty($auraConfigs)) {
      return $auraConfigs;
    }

    $auraApiConfigKeys = $this->getAuraApiConfigKeys();

    foreach ($auraApiConfigKeys as $value) {
      $cache_key = 'alshaya_aura_react:aura_api_configs:' . $value;
      $cache = $this->cache->get($cache_key);

      if (is_object($cache) && !empty($cache->data)) {
        $auraConfigs[$value] = $cache->data;
      }
      else {
        $response = $this->apiWrapper->invokeApi('customers/apcDicData/' . $value, [], 'GET');

        // @TODO: Remove hard coded response once API is funtional.
        $response = [
          "code" => "APC_CASHBACK_ACCRUAL_RATIO",
          "items" => [
            [
              "code" => "KWD",
              "order" => 0,
              "value" => "10",
            ],
            [
              "code" => "SAR",
              "order" => 1,
              "value" => "1",
            ],
          ],
        ];

        if (empty($response)) {
          $this->logger->error('Empty response from aura config api: @api.', [
            '@api' => $endpoint,
          ]);
        }

        $auraConfigs[$value] = $response;
        $this->cache->set($cache_key, $response, Cache::PERMANENT);
      }
    }

    return $auraConfigs;
  }

  /**
   * Get Aura api config keys.
   *
   * @return array
   *   Return array of config values.
   */
  private function getAuraApiConfigKeys() {
    $auraApiConfigKeys = [
      'APC_CASHBACK_ACCRUAL_RATIO',
      'APC_CASHBACK_REDEMPTION_RATIO',
      'EXT_PHONE_PREFIX',
    ];

    return $auraApiConfigKeys;
  }

}
