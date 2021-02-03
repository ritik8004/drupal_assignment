<?php

namespace Drupal\alshaya_bnpl\Helper;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Site\Settings;

/**
 * Api helper for Postpay.
 *
 * @package Drupal\alshaya_bnpl\Helper
 */
class AlshayaBnplAPIHelper {

  use LoggerChannelTrait;

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
   * Cache backend postpay.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * AlshayaBnplAPIHelper constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend checkout_com.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    CacheBackendInterface $cache
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->cache = $cache;

    $this->logger = $this->getLogger('AlshayaBnplAPIHelper');
  }

  /**
   * Get BNPL config.
   *
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getBnplApiConfig($reset = FALSE) {
    static $configs;

    if (!empty($configs)) {
      return $configs;
    }

    $cache_key = 'alshaya_bnpl:api_configs';

    // Cache time in minutes, set 0 to disable caching.
    $cache_time = (int) Settings::get('postpay_cache_time', 60);

    // Disable caching if cache time set to 0 or null in settings.
    $reset = empty($cache_time) ? TRUE : $reset;

    $cache = $reset ? NULL : $this->cache->get($cache_key);
    if (is_object($cache) && !empty($cache->data)) {
      $configs = $cache->data;
    }
    else {
      $response = $this->apiWrapper->invokeApi(
        'postpay/config',
        [],
        'GET'
      );

      $configs = Json::decode($response);
      if (!isset($configs['theme']) || empty($configs['theme'])) {
        $configs['theme'] = 'light';
      }
      $configs['sandbox'] = isset($configs['environment']) && $configs['environment'] == 'sandbox' ? TRUE : FALSE;

      if (empty($configs)) {
        $this->logger->error('Invalid response from Postpay api, @response', [
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
      return $this->getBnplApiConfig(TRUE);
    }

    return $configs;
  }

}
