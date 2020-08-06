<?php

namespace App\Helper;

use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Class Cache.
 *
 * @package App\Cache
 */
class Cache {

  const CACHE_EXPIRY_SECONDS = 604800;

  /**
   * Cache Interface.
   *
   * @var \Symfony\Contracts\Cache\TagAwareCacheInterface
   */
  protected $cache;

  /**
   * Cache constructor.
   *
   * @param \Symfony\Contracts\Cache\TagAwareCacheInterface $appointmentCache
   *   Cache Interface.
   */
  public function __construct(TagAwareCacheInterface $appointmentCache) {
    $this->cache = $appointmentCache;
  }

  /**
   * Get cached Item.
   *
   * @param string $key
   *   Cache key.
   *
   * @return mixed
   *   Cache data or false.
   */
  public function getItem($key) {
    $item = $this->cache->getItem($key);
    if ($item->isHit()) {
      // Item exists.
      $cachedItem = $item->get();
      return $cachedItem;
    }
    return FALSE;
  }

  /**
   * Set Cache data.
   */
  public function setItem($key, $data) {
    /** @var \Symfony\Contracts\Cache\ItemInterface $item */
    $item = $this->cache->getItem($key);
    $item
      ->set($data)
      ->tag($key)
    // In seconds.
      ->expiresAfter($this::CACHE_EXPIRY_SECONDS);
    $this->cache->save($item);
  }

  /**
   * Provides cache client.
   */
  public function getCacheClient() {
    return $this->cache;
  }

  /**
   * Cache Provider.
   */
  public function createConnection() {
    return MemcachedAdapter::createConnection('memcached://localhost');
  }

}
