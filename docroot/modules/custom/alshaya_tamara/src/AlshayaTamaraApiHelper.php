<?php

namespace Drupal\alshaya_tamara;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;

/**
 * Helper class for Tamara.
 *
 * @package Drupal\alshaya_tamara
 */
class AlshayaTamaraApiHelper {

  /**
   * Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Cache backend bnpl.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * AlshayaBnplApiHelper Constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper,
                              CacheBackendInterface $cache,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->cache = $cache;
    $this->logger = $logger_factory->get('AlshayaTamaraApiHelper');
  }

  /**
   * Get Tamara payment method config.
   *
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getTamaraApiConfig($reset = FALSE) {
    static $configs;

    if (!empty($configs)) {
      return $configs;
    }

    $cache_key = 'alshaya_tamara:api_configs';

    // Cache time in minutes, set 0 to disable caching.
    $cache_time = (int) Settings::get('alshaya_tamara_cache_time', 60);

    // Disable caching if cache time set to 0 or null in settings.
    $reset = empty($cache_time) ? TRUE : $reset;

    $cache = $reset ? NULL : $this->cache->get($cache_key);
    if (is_object($cache) && !empty($cache->data)) {
      $configs = $cache->data;
    }
    else {
      $request_options = [
        'timeout' => $this->apiWrapper->getMagentoApiHelper()->getPhpTimeout('tamara_config'),
      ];
      $response = $this->apiWrapper->invokeApi(
        'tamara/config',
        [],
        'GET',
        FALSE,
        $request_options
      );

      $configs = Json::decode($response);

      if (empty($configs)) {
        $this->logger->error('Invalid response from Tamara api, @response', [
          '@response' => Json::encode($configs),
        ]);
      }
      elseif ($cache_time > 0) {
        // Cache only if enabled (cache_time set).
        $this->cache->set($cache_key, $configs, strtotime("+${cache_time} minutes"));
      }
    }

    // Try resetting once.
    if (empty($configs) && !($reset)) {
      return $this->getTamaraApiConfig(TRUE);
    }

    return $configs;
  }

}
