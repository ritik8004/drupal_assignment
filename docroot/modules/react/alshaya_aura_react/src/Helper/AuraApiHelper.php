<?php

namespace Drupal\alshaya_aura_react\Helper;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\alshaya_aura_react\Constants\AuraDictionaryApiConstants;
use Drupal\Component\Serialization\Json;

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
   * Mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * AuraApiHelper constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend service for aura_api_config.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    LoggerChannelFactoryInterface $logger_factory,
    CacheBackendInterface $cache,
    MobileNumberUtilInterface $mobile_util
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('alshaya_aura_react');
    $this->cache = $cache;
    $this->mobileUtil = $mobile_util;
  }

  /**
   * Get Aura config from API.
   *
   * @return array
   *   Return array of config values.
   */
  public function getAuraApiConfig($configs = [], $reset = FALSE) {
    static $auraConfigs;

    $auraApiConfig = !empty($configs)
      ? $configs
      : AuraDictionaryApiConstants::ALL_DICTIONARY_API_CONSTANTS;

    $notFound = FALSE;
    foreach ($auraApiConfig as $config) {
      if (empty($auraConfigs[$config])) {
        $notFound = TRUE;
      }
    }

    if ($notFound === FALSE) {
      return $auraConfigs;
    }

    foreach ($auraApiConfig as $value) {
      $cache_key = 'alshaya_aura_react:aura_api_configs:' . $value;

      if (!$reset && $cache = $this->cache->get($cache_key)) {
        $auraConfigs[$value] = $cache->data;
        continue;
      }

      $endpoint = 'customers/apcDicData/' . $value;
      $response = $this->apiWrapper->invokeApi($endpoint, [], 'GET');
      $response = is_string($response) ? Json::decode($response) : $response;

      if (empty($response) || empty($response['items'])) {
        $this->logger->error('No data found for api: @api.', [
          '@api' => $endpoint,
        ]);
        continue;
      }

      // Getting `code` and `value` keys for tier types api
      // and just value for others.
      $data = ($value === AuraDictionaryApiConstants::APC_TIER_TYPES)
        ? array_column($response['items'], 'value', 'code')
        : array_column($response['items'], 'value');

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
    ];

    return $data;
  }

}
