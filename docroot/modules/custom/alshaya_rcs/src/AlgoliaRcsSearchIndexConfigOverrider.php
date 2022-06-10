<?php

namespace Drupal\alshaya_rcs;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Site\Settings;

/**
 * Class to override the Algolia Index configs dynamically based on Settings.
 */
class AlgoliaRcsSearchIndexConfigOverrider implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    static $overrides = [];

    if (!empty($overrides)) {
      return $overrides;
    }

    // Make indices read-only.
    $overrides['search_api.index.alshaya_algolia_index']['read_only'] = TRUE;
    $overrides['search_api.index.acquia_search_index']['read_only'] = TRUE;
    $overrides['search_api.index.alshaya_algolia_product_list_index']['read_only'] = TRUE;

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'AlgoliaRcsSearchIndexConfigOverrider';
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
    $metadata = new CacheableMetadata();
    $metadata->setCacheContexts(['languages:language_interface']);
    return $metadata;
  }

}
