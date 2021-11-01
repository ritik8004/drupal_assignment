<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Helper class for BNPL.
 *
 * @package Drupal\alshaya_acm_checkout
 */
class AlshayaBnplApiHelper {

  use StringTranslationTrait;

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
   *   Cache backend checkout_com.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper,
                              CacheBackendInterface $cache,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->cache = $cache;
    $this->logger = $logger_factory->get('AlshayaBnplHelper');
  }

  /**
   * Get BNPL payment method config.
   *
   * @param string $payment_method
   *   Payment method to look for.
   * @param string $endpoint
   *   Endpoint to use.
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getBnplApiConfig($payment_method, $endpoint, $reset = FALSE) {

    static $configs;

    if (!empty($configs)) {
      return $configs;
    }

    $cache_key = "{$payment_method}:api_configs";

    // Cache time in minutes, set 0 to disable caching.
    $cache_time = (int) Settings::get("{$payment_method}_cache_time", 60);

    // Disable caching if cache time set to 0 or null in settings.
    $reset = empty($cache_time) ? TRUE : $reset;

    $cache = $reset ? NULL : $this->cache->get($cache_key);
    if (is_object($cache) && !empty($cache->data)) {
      $configs = $cache->data;
    }
    else {
      $response = $this->apiWrapper->invokeApi(
        $endpoint,
        [],
        'GET'
      );

      $configs = Json::decode($response);

      if (empty($configs)) {
        $this->logger->error('Invalid response from BNPL api, Payment Method: @payment_method, @response', [
          '@payment_method' => $payment_method,
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
      return $this->getBnplApiConfig($payment_method, $endpoint, TRUE);
    }

    // @todo replace with $configs if mdc api works fine.
    return [
      'merchant_code' => 'uae_test',
      'public_key' => 'pk_test_99a77d42-a084-4fff-aee1-a8587483aa13',
    ];
  }

}
