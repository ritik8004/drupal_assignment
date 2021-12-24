<?php

namespace Drupal\alshaya_rcs;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Site\Settings;

/**
 * Class to override the Algolia Index name dynamically based on Settings.
 */
class AlgoliaRcsSearchIndexNameConfigOverrider implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    static $overrides = [];

    if (!empty($overrides)) {
      return $overrides;
    }

    $site_info = alshaya_get_site_country_code();
    $algolia_env = Settings::get('algolia_env');
    // Use local env for travis.
    $algolia_env = $algolia_env === 'travis' ? 'local' : $algolia_env;

    $overrides['search_api.index.alshaya_algolia_index']['options']['algolia_index_name'] = $algolia_env . '_' . $site_info['country_code'];
    $overrides['search_api.index.acquia_search_index']['options']['algolia_index_name'] = $algolia_env . '_' . $site_info['country_code'];
    $overrides['search_api.index.alshaya_algolia_product_list_index']['options']['algolia_index_name'] = $algolia_env . '_' . $site_info['country_code'] . '_product_list';

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'AlgoliaIndexNameConfigOverrider';
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
