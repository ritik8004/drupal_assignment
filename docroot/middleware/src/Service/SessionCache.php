<?php

namespace App\Service;

/**
 * Class SessionCache.
 *
 * @package App\Service
 */
class SessionCache {

  /**
   * Session Storage.
   *
   * @var \App\Service\SessionStorage
   */
  protected $storage;

  /**
   * SessionCache constructor.
   *
   * @param \App\Service\SessionStorage $storage
   *   Session Storage.
   */
  public function __construct(SessionStorage $storage) {
    $this->storage = $storage;
  }

  /**
   * Get data from cache.
   *
   * @param string $key
   *   Cache key.
   *
   * @return mixed|null
   *   Data from cache if available.
   */
  public function get(string $key) {
    $data = $this->storage->getDataFromSession($key);
    if (empty($data)) {
      return NULL;
    }

    if ($data['expire'] < (int) $_SERVER['REQUEST_TIME']) {
      return NULL;
    }

    return $data['data'];
  }

  /**
   * Store data in cache.
   *
   * @param string $key
   *   Cache key.
   * @param int $expire
   *   Time in seconds after which the cache should expire.
   * @param mixed $value
   *   Value to store in cache.
   */
  public function set(string $key, int $expire, $value) {
    $expire += (int) $_SERVER['REQUEST_TIME'];
    $data = [
      'expire' => $expire,
      'data' => $value,
    ];

    $this->storage->updateDataInSession($key, $data);
  }

  /**
   * Delete data in cache.
   *
   * @param string $key
   *   Cache key.
   */
  public function delete(string $key) {
    $this->storage->updateDataInSession($key, NULL);
  }

}
