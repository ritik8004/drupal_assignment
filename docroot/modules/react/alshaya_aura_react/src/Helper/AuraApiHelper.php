<?php

namespace Drupal\alshaya_aura_react\Helper;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\alshaya_aura_react\Constants\AuraDictionaryApiConstants;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Language\LanguageManagerInterface;

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
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    LoggerChannelFactoryInterface $logger_factory,
    CacheBackendInterface $cache,
    MobileNumberUtilInterface $mobile_util,
    LanguageManagerInterface $language_manager
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('alshaya_aura_react');
    $this->cache = $cache;
    $this->mobileUtil = $mobile_util;
    $this->languageManager = $language_manager;
  }

  /**
   * Get Aura config from API.
   *
   * @return array
   *   Return array of config values.
   */
  public function getAuraApiConfig($configs = [], $langcode = 'en', $reset = FALSE) {
    $auraApiConfig = !empty($configs)
      ? $configs
      : AuraDictionaryApiConstants::ALL_DICTIONARY_API_CONSTANTS;

    foreach ($auraApiConfig as $value) {
      // Adding language code in cache key for tier names only
      // because for rest of the dictionary APIs, response is not
      // expected to change based on language.
      $cache_key = $value === AuraDictionaryApiConstants::APC_TIER_TYPES
        ? 'alshaya_aura_react:aura_api_configs:' . $langcode . ':' . $value
        : 'alshaya_aura_react:aura_api_configs:' . $value;

      if (!$reset && $cache = $this->cache->get($cache_key)) {
        $auraConfigs[$value] = $cache->data;
        continue;
      }

      // For tier mapping API, if langcode in the argument is different from
      // the request language then update context langcode for the API call.
      $resetStoreContext = FALSE;
      if ($value === AuraDictionaryApiConstants::APC_TIER_TYPES
        && $langcode !== $this->languageManager->getCurrentLanguage()->getId()) {
        $this->apiWrapper->updateStoreContext($langcode);
        $resetStoreContext = TRUE;
      }

      $endpoint = 'customers/apcDicData/' . $value;
      $response = $this->apiWrapper->invokeApi($endpoint, [], 'GET');
      $response = is_string($response) ? Json::decode($response) : $response;

      // Restore the store context langcode.
      if ($resetStoreContext) {
        $this->apiWrapper->resetStoreContext();
      }

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
    $aura_dictionary_api_config = $this->getAuraApiConfig(
      [AuraDictionaryApiConstants::CASHBACK_ACCRUAL_RATIO,
        AuraDictionaryApiConstants::CASHBACK_REDEMPTION_RATIO,
        AuraDictionaryApiConstants::RECOGNITION_ACCRUAL_RATIO,
      ],
      $this->languageManager->getCurrentLanguage()->getId());

    $data = [
      'priceToPointRatio' => $aura_dictionary_api_config[AuraDictionaryApiConstants::CASHBACK_ACCRUAL_RATIO] ?? '',
      'pointToPriceRatio' => $aura_dictionary_api_config[AuraDictionaryApiConstants::CASHBACK_REDEMPTION_RATIO] ?? '',
      'recognitionAccrualRatio' => $aura_dictionary_api_config[AuraDictionaryApiConstants::RECOGNITION_ACCRUAL_RATIO] ?? '',
    ];

    return $data;
  }

}
