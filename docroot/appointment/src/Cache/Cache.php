<?php

namespace App\Cache;

use Doctrine\DBAL\Connection;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Class Cache.
 *
 * @package App\Cache
 */
class Cache {

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
  public function __construct(Connection $connection) {
    $cache = new PdoAdapter($connection);
    $this->cache = new TagAwareAdapter($cache);
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
    $expire = (int) $_ENV['CACHE_EXPIRY_SECONDS'];
    /** @var \Symfony\Contracts\Cache\ItemInterface $item */
    $item = $this->cache->getItem($key);
    $item
      ->set($data)
      ->tag($key)
    // In seconds.
      ->expiresAfter($expire);
    $this->cache->save($item);
  }

  /**
   * Provides cache client.
   */
  public function getCacheClient() {
    return $this->cache;
  }

  public function deleteCacheItem($key) {
    $this->cache->deleteItem($key);
  }

  public function cacheClear() {
    $this->cache->clear();
  }

}
