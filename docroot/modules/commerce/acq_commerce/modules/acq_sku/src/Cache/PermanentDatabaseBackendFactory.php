<?php

namespace Drupal\acq_sku\Cache;

use Drupal\Core\Cache\DatabaseBackendFactory;

class PermanentDatabaseBackendFactory extends DatabaseBackendFactory {

  /**
   * Gets DatabaseBackend for the specified cache bin.
   *
   * @param $bin
   *   The cache bin for which the object is created.
   *
   * @return \Drupal\Core\Cache\DatabaseBackend
   *   The cache backend object for the specified cache bin.
   */
  public function get($bin) {
    return new PermanentDatabaseBackend($this->connection, $this->checksumProvider, $bin);
  }

}
