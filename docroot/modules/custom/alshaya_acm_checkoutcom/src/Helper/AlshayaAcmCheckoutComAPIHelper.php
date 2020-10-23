<?php

namespace Drupal\alshaya_acm_checkoutcom\Helper;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Component\Serialization\Json;

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
   * @param string|null $type
   *   Type of key, public_key, env etc.
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getCheckoutcomUpApiConfig(?string $type, $reset = FALSE) {
    $cache_key = 'alshaya_acm_checkoutcom:api_configs';

    $cache = $reset ? NULL : $this->cache->get($cache_key);

    if (empty($cache) || empty($cache->data)) {
      $response = $this->apiWrapper->invokeApi(
        'checkoutcomupapi/config',
        [],
        'GET'
      );
      $configs = Json::decode($response);

      if (!empty($configs)) {
        $this->cache->set($cache_key, $configs);
      }
    }
    else {
      $configs = $cache->data;
    }

    if (empty($configs)) {
      if ($reset) {
        $this->logger->error('Invalid response from checkout.com upapi api, @response', [
          '@response' => Json::encode($configs),
        ]);

        return NULL;
      }

      // Try resetting once.
      return $this->getCheckoutcomUpApiConfig($type, TRUE);
    }

    return $type ? $configs[$type] : $configs;
  }

}
