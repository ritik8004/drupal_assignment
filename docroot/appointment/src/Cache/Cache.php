<?php

namespace App\Cache;

use Doctrine\DBAL\Connection;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

/**
 * Class Cache.
 *
 * @package App\Cache
 */
class Cache {

  const APPOINTMENT_CACHE_TABLE = 'appointment_cache';

  /**
   * Cache Interface.
   *
   * @var \Symfony\Contracts\Cache\TagAwareCacheInterface
   */
  protected $cache;

  /**
   * Cache constructor.
   *
   * @param \Doctrine\DBAL\Connection $connection
   *   Db connection object.
   */
  public function __construct(Connection $connection) {
    $options = [
      'db_table' => self::APPOINTMENT_CACHE_TABLE,
    ];
    $cache = new PdoAdapter($connection, '', 0, $options);
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
    // In seconds.
      ->expiresAfter($expire);
    $this->cache->save($item);
  }

  /**
   * Set Cache data.
   */
  public function setItemWithTags($key, $data, $tags) {
    $expire = (int) $_ENV['CACHE_EXPIRY_SECONDS'];
    /** @var \Symfony\Contracts\Cache\ItemInterface $item */
    $item = $this->cache->getItem($key);
    $item
      ->set($data)
      ->tag($tags)
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

  /**
   * Delete Cache item.
   */
  public function deleteCacheItem($key) {
    $this->cache->deleteItem($key);
  }

  /**
   * Clear all cache.
   */
  public function cacheClear() {
    $this->cache->clear();
  }

}
