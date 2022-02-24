<?php

namespace Drupal\alshaya_rcs;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Class to set Algolia indexes to read-only.
 *
 * When RCS is enabled, we will not be storing product data in Drupal.
 * Hence we will not be updating Algolia indices from Drupal and so we make
 * them read-only to prevent updating them in anyway.
 */
class AlgoliaRcsSearchIndexCrudStatusConfigOverrider implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    static $overrides = [];

    if (!empty($overrides)) {
      return $overrides;
    }

    $overrides['search_api.index.alshaya_algolia_index']['read_only'] = TRUE;
    $overrides['search_api.index.acquia_search_index']['read_only'] = TRUE;
    $overrides['search_api.index.alshaya_algolia_product_list_index']['read_only'] = TRUE;

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'AlgoliaRcsSearchIndexCrudStatusConfigOverrider';
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

}
