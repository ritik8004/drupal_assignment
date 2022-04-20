<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Site\Settings;
use Drupal\alshaya_api\Helper\MagentoApiHelper;

/**
 * Api helper for promo label config.
 *
 * @package Drupal\alshaya_acm_promotion
 */
class AlshayaAcmPromoLabelAPIHelper {

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
  protected $drupalLogger;

  /**
   * Cache backend discount_text.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The mdc helper.
   *
   * @var \Drupal\alshaya_api\Helper\MagentoApiHelper
   */
  protected $mdcHelper;

  /**
   * AlshayaAcmPromoLabelAPIHelper constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend discount_text.
   * @param \Drupal\alshaya_api\Helper\MagentoApiHelper $mdc_helper
   *   The magento api helper.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    CacheBackendInterface $cache,
    MagentoApiHelper $mdc_helper
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->cache = $cache;
    $this->mdcHelper = $mdc_helper;

    $this->logger = $this->getLogger('AlshayaAcmPromoLabelAPIHelper');
  }

  /**
   * Get Promo label config.
   *
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getPromoLabelApiConfig($reset = FALSE) {
    static $status;

    if (is_bool($status)) {
      return $status;
    }

    $cache_key = 'alshaya_acm_promotion:promo_lable_api_status';

    // Cache time in minutes, set 0 to disable caching.
    $cache_time = (int) Settings::get('alshaya_acm_promotion_label_api_config_cache_time', 5);

    // Disable caching if cache time set to 0 or null in settings.
    $reset = empty($cache_time) ? TRUE : $reset;
    $cache = $reset ? NULL : $this->cache->get($cache_key);
    if (is_object($cache) && !empty($cache->data)) {
      $status = $cache->data;
    }
    else {
      $request_options = [
        'timeout' => $this->mdcHelper->getPhpTimeout('discount_text'),
      ];

      $response = $this->apiWrapper->invokeApi(
        'promotion/get-config',
        [],
        'GET',
        FALSE,
        $request_options
      );

      $status = Json::decode($response);

      if (is_bool($status)) {
        // Cache only if enabled (cache_time set).
        $this->cache->set($cache_key, $status, strtotime("+${cache_time} minutes"));
      }
      else {
        $this->logger->error('Invalid response from promo label config api, @response', [
          '@response' => Json::encode($status),
        ]);
      }
    }

    // Try resetting once.
    if (!is_bool($status) && !($reset)) {
      return $this->getPromoLabelApiConfig(TRUE);
    }

    return $status;
  }

}
