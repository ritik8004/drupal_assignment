<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\alshaya_api\Helper\MagentoApiHelper;

/**
 * Api helper for promotion config.
 *
 * @package Drupal\alshaya_acm_promotion
 */
class AlshayaAcmPromotionAPIHelper {

  /**
   * Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Cache backend alshaya_acm_promotion.
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
   * AlshayaAcmPromotionAPIHelper constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend alshaya_acm_promotion.
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
  }

  /**
   * Get the discount text visibility status.
   *
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function getDiscountTextVisibilityStatus($reset = FALSE) {
    static $status;

    if (!$reset && isset($status)) {
      return $status;
    }

    $cache_key = 'alshaya_acm_promotion:discount_text_visibility_status';
    $cache = $reset ? NULL : $this->cache->get($cache_key);

    if (is_object($cache) && isset($cache->data)) {
      $status = $cache->data;
      if (!$reset) {
        return $status;
      }
    }

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('discount_text'),
    ];

    // Set status to false by default if not set already
    // as that is the default value.
    $status ??= FALSE;

    // Try to get response from API as either value is not set in cache
    // or we are asked to reset.
    $response = $this->apiWrapper->invokeApi(
      'promotion/get-config',
      [],
      'GET',
      FALSE,
      $request_options
    );

    if ($response === 'true' || $response === 'false') {
      $status = $response;
    }

    // Update value in cache.
    $this->cache->set($cache_key, $status);

    return $status;
  }

}
