<?php

namespace Drupal\alshaya_master;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\State\StateInterface;

/**
 * Class to override the Acquia Search server to use Solr backend.
 */
class AlshayaSolrConfigOverrider implements ConfigFactoryOverrideInterface {

  /**
   * Drupal State Service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * AlshayaSolrConfigOverrider constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   Drupal State Service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    static $overrides = [];
    // Do not apply any overrides till the site is completely installed.
    if ($this->state->get('alshaya_master_post_drupal_install') !== 'done') {
      return $overrides;
    }

    // Return if current profile is alshaya_transac or existing overrides.
    if (!empty($overrides) || 'alshaya_transac' === \Drupal::installProfile()) {
      return $overrides;
    }

    // Connect to Acquia Cloud Search server on Cloud.
    $ah_env = getenv('AH_SITE_ENVIRONMENT');
    if ($ah_env && $ah_env !== 'ide') {
      $overrides['search_api.server.acquia_search_server']['name'] = 'Acquia Search API Solr server';
      $overrides['search_api.server.acquia_search_server']['backend_config']['connector'] = 'solr_acquia_connector';
    }
    // Connect to local solr server in local and travis.
    else {
      $overrides['search_api.server.acquia_search_server']['name'] = 'Local Solr server';
      $overrides['search_api.server.acquia_search_server']['backend_config']['connector'] = 'standard';
      $overrides['search_api.server.acquia_search_server']['backend_config']['connector_config']['host'] = 'localhost';
      $overrides['search_api.server.acquia_search_server']['backend_config']['connector_config']['port'] = '8983';
      $overrides['search_api.server.acquia_search_server']['backend_config']['connector_config']['path'] = '/solr';
      $overrides['search_api.server.acquia_search_server']['backend_config']['connector_config']['core'] = '';
      $overrides['search_api.server.acquia_search_server']['backend_config']['connector_config']['commit_within'] = '1000';
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'AlshayaSolrConfigOverrider';
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
