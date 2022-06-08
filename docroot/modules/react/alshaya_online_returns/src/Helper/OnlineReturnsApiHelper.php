<?php

namespace Drupal\alshaya_online_returns\Helper;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_api\Helper\MagentoApiHelper;
use Drupal\Component\Serialization\Json;

/**
 * Helper class for Online Returns APIs.
 *
 * @package Drupal\alshaya_online_returns\Helper
 */
class OnlineReturnsApiHelper {

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
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The mdc helper.
   *
   * @var \Drupal\alshaya_api\Helper\MagentoApiHelper
   */
  protected $mdcHelper;

  /**
   * OnlineReturnsApiHelper constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend service for aura_api_config.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\alshaya_api\Helper\MagentoApiHelper $mdc_helper
   *   The magento api helper.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    LoggerChannelFactoryInterface $logger_factory,
    CacheBackendInterface $cache,
    LanguageManagerInterface $language_manager,
    MagentoApiHelper $mdc_helper
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('alshaya_online_returns');
    $this->cache = $cache;
    $this->languageManager = $language_manager;
    $this->mdcHelper = $mdc_helper;
  }

  /**
   * Get Online Returns config from API.
   *
   * @return array
   *   Return array of config values.
   */
  public function getReturnsApiConfig($langcode = 'en', $reset = FALSE) {
    $cache_key = 'alshaya_online_returns:returns_api_config:' . $langcode;
    $cache = $this->cache->get($cache_key);

    // Return the cache info only if the value exists.
    if (!$reset && $cache && $cache->data) {
      return $cache->data;
    }

    // If langcode in the argument is different from the request language
    // then update context langcode for the API call.
    $resetStoreContext = FALSE;
    if ($langcode !== $this->languageManager->getCurrentLanguage()->getId()) {
      $this->apiWrapper->updateStoreContext($langcode);
      $resetStoreContext = TRUE;
    }

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('online_returns_config'),
    ];
    $configs = [];
    $endpoint = 'returnsconfig';
    $response = $this->apiWrapper->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

    if ($response && is_string($response)) {
      $configs = Json::decode($response);
    }

    // Restore the store context langcode.
    if ($resetStoreContext) {
      $this->apiWrapper->resetStoreContext();
    }

    if (empty($configs)) {
      $this->logger->error('No data found for api: @api. Response: @response', [
        '@api' => $endpoint,
        '@response' => Json::encode($response),
      ]);
      // Update the config with the old cache value.
      if ($cache->data) {
        $configs = $cache->data;
      }
    }
    else {
      $this->cache->set($cache_key, $configs, Cache::PERMANENT);
    }

    return $configs;
  }

}
