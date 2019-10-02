<?php

namespace Drupal\alshaya_acm_product\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Class ProductCacheManager.
 *
 * @package Drupal\alshaya_acm_product\Service
 */
class ProductCacheManager {

  /**
   * Cache backend for product_processed_data cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * ProductCacheManager constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend for product_processed_data cache.
   */
  public function __construct(CacheBackendInterface $cache) {
    $this->cache = $cache;
  }

  /**
   * Get value from cache.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   * @param string $key
   *   Cache Key.
   *
   * @return mixed
   *   Value from cache if found.
   */
  public function get(SKU $sku, string $key) {
    $cid = $this->getSkuCacheId($sku, $key);

    $static = &drupal_static('alshaya_product_processed_cached', []);

    if (!isset($static[$cid])) {
      $cache = $this->cache->get($cid);
      $static[$cid] = $cache->data ?? NULL;
    }

    return $static[$cid];
  }

  /**
   * Set value in cache.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   * @param string $key
   *   Cache Key.
   * @param mixed $data
   *   Data to set in cache.
   * @param array|null $tags
   *   Additional cache tags.
   */
  public function set(SKU $sku, string $key, $data, ?array $tags = []) {
    $cid = $this->getSkuCacheId($sku, $key);

    $tags = is_array($tags) ? Cache::mergeTags($tags, $sku->getCacheTags()) : $sku->getCacheTags();
    $this->cache->set($cid, $data, Cache::PERMANENT, $tags);

    $static = &drupal_static('alshaya_product_processed_cached', []);
    $static[$cid] = $data;
  }

  /**
   * Get cache key for SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   * @param string $key
   *   Cache Key.
   *
   * @return string
   *   Cache key.
   */
  private function getSkuCacheId(SKU $sku, string $key) {
    return implode(':', [
      $sku->id(),
      $sku->language()->getId(),
      $key,
    ]);
  }

}
