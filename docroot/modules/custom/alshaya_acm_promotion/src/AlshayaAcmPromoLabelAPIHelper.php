<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\alshaya_api\Helper\MagentoApiHelper;

/**
 * Api helper for promo label config.
 *
 * @package Drupal\alshaya_acm_promotion
 */
class AlshayaAcmPromoLabelAPIHelper {

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
   * AlshayaAcmPromoLabelAPIHelper constructor.
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
   * Get Promo label config.
   *
   * @param bool $reset
   *   Reset cached data and fetch again.
   *
   * @return array|mixed
   *   Return array of keys.
   */
  public function hideDiscountedText($reset = FALSE) {
    static $status;

    if ($status === 'true' && !$reset) {
      return $status;
    }

    $cache_key = 'alshaya_acm_promotion:promo_lable_api_status';
    $cache = $reset ? NULL : $this->cache->get($cache_key);
    if (is_object($cache) && $cache->data === 'true' && !$reset) {
      $status = $cache->data;
      return $status;
    }

    $request_options = [
      'timeout' => $this->mdcHelper->getPhpTimeout('discount_text'),
    ];

    $status = $this->apiWrapper->invokeApi(
      'promotion/get-config',
      [],
      'GET',
      FALSE,
      $request_options
    );

    if ($status === 'true') {
      // Cache only if enabled.
      $this->cache->set($cache_key, $status);
    }
    else {
      // Cache only if not enabled.
      $this->cache->set($cache_key, FALSE);
    }

    // Try resetting once.
    if ($status !== 'true' && !$reset) {
      return $this->hideDiscountedText(TRUE);
    }

    return FALSE;
  }

}
