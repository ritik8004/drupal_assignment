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
      : AuraDictionaryApiConstants::ALL_DICTIONARY_API_CONSTANTS;

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
      if ($value === 'EXT_PHONE_PREFIX') {
        $response = [
          "code" => "EXT_PHONE_PREFIX",
          "items" => [
            [
              "code" => "+973",
              "order" => 0,
              "value" => "+973",
            ],
            [
              "code" => "+966",
              "order" => 1,
              "value" => "+966",
            ],
          ],
        ];
      }

      if (empty($response)) {
        $this->logger->error('No data found for api: @api.', [
          '@api' => $endpoint,
        ]);
        continue;
      }

      $data = !empty($response['items']) ? array_column($response['items'], 'value') : [];

      $auraConfigs[$value] = $data;
      $this->cache->set($cache_key, $auraConfigs[$value], Cache::PERMANENT);
    }

    return $auraConfigs;
  }

  /**
   * Prepare aura dictionary api data.
   *
   * @return array
   *   AURA dictionary api data.
   */
  public function prepareAuraDictionaryApiData() {
    $aura_dictionary_api_config = $this->getAuraApiConfig();

    $data = [
      'priceToPointRatio' => $aura_dictionary_api_config[AuraDictionaryApiConstants::CASHBACK_ACCRUAL_RATIO] ?? '',
      'pointToPriceRatio' => $aura_dictionary_api_config[AuraDictionaryApiConstants::CASHBACK_REDEMPTION_RATIO] ?? '',
      'phonePrefixList' => $aura_dictionary_api_config[AuraDictionaryApiConstants::EXT_PHONE_PREFIX] ?? '',
    ];

    return $data;
  }

}
