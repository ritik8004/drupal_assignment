<?php

namespace App\Cache;

use Doctrine\DBAL\Connection;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

/**
 * Helper class for appointment cache.
 *
 * @package App\Cache
 */
class Cache {

  public const APPOINTMENT_CACHE_TABLE = 'appointment_cache';

  /**
   * Cache Expiration in seconds.
   *
   * @var int
   */
  protected $cacheExpiration;

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
    $this->cacheExpiration = (int) $_ENV['APPOINTMENT_CACHE_EXPIRY'];
    $options = [
      'db_table' => self::APPOINTMENT_CACHE_TABLE,
    ];
    $this->cache = new PdoAdapter($connection, '', 0, $options);
  }

  /**
   * Get cached Item.
   *
   * @param string $key
   *   Cache key.
   * @param string $langcode
   *   Language code.
   *
   * @return mixed
   *   Cache data or false.
   */
  public function getItem($key, $langcode = '') {
    // If langcode is passed append to the key.
    $key = (!empty($langcode)) ? $key . '_' . $langcode : $key;
    $item = $this->cache->getItem($key);
    if ($item->isHit()) {
      // Item exists.
      $cachedItem = $item->get();
      return $cachedItem;
    }
    return FALSE;
  }

  /**
   * Get cached Item.
   *
   * @param string $key
   *   Cache key.
   * @param string $langcode
   *   Language code.
   *
   * @return mixed
   *   Cache data or false.
   */
  public function getItemByTagAware($key, $langcode = '') {
    // If langcode is passed append to the key.
    $key = (!empty($langcode)) ? $key . '_' . $langcode : $key;
    $cache = new TagAwareAdapter($this->cache);
    $item = $cache->getItem($key);
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
  public function setItem($key, $data, $langcode = '') {
    // If langcode is passed append to the key.
    $key = (!empty($langcode)) ? $key . '_' . $langcode : $key;
    /** @var \Symfony\Contracts\Cache\ItemInterface $item */
    $item = $this->cache->getItem($key);
    $item
      ->set($data)
    // In seconds.
      ->expiresAfter($this->cacheExpiration);
    $this->cache->save($item);
  }

  /**
   * Set Cache with tags.
   */
  public function setItemWithTags($key, $data, $tags, $langcode = '') {
    // If langcode is passed append to the key.
    $key = (!empty($langcode)) ? $key . '_' . $langcode : $key;
    $cache = new TagAwareAdapter($this->cache);
    /** @var \Symfony\Contracts\Cache\ItemInterface $item */
    $item = $cache->getItem($key);
    $item
      ->set($data)
      ->tag($tags)
      // In seconds.
      ->expiresAfter($this->cacheExpiration);
    $cache->save($item);
  }

  /**
   * Provides cache client.
   */
  public function getCacheClient() {
    return $this->cache;
  }

  /**
   * Invalidate cache tags.
   */
  public function tagInvalidation($tags) {
    $cache = new TagAwareAdapter($this->cache);
    return $cache->invalidateTags($tags);
  }

}
