<?php

namespace Drupal\alshaya_search_algolia;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;

/**
 * Class to override the Algolia Index name dynamically.
 */
class AlgoliaSearchIndexNameConfigOverrider implements ConfigFactoryOverrideInterface {

  /**
   * Drupal State Service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Algolia search config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $alshayaAlgoliaConfig;

  /**
   * AlgoliaSearchIndexNameConfigOverrider constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   Drupal State Service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   */
  public function __construct(
    StateInterface $state,
    ConfigFactoryInterface $config_factory
  ) {
    $this->state = $state;
    $this->configFactory = $config_factory;
    // We load the config here in order to prevent a recursive loop as this
    // class is a config overrider class.
    $this->alshayaAlgoliaConfig = $this->configFactory->get('alshaya_search_algolia.settings');
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

    if (!empty($overrides)) {
      return $overrides;
    }

    // Disable Database Index.
    // We still need to work on cleanup and for fresh install to keep working
    // it is still required.
    $overrides['search_api.index.product']['status'] = FALSE;

    // Default overrides for acquia_search_index.
    $overrides['search_api.index.acquia_search_index']['read_only'] = TRUE;
    $overrides['search_api.index.acquia_search_index']['status'] = TRUE;
    $overrides['search_api.index.acquia_search_index']['server'] = 'algolia';
    $overrides['search_api.index.acquia_search_index']['options']['algolia_index_apply_suffix'] = TRUE;
    $overrides['search_api.index.acquia_search_index']['options']['algolia_index_list'] = '';

    $overrides['search_api.index.alshaya_algolia_index']['options']['algolia_index_apply_suffix'] = TRUE;
    $overrides['search_api.index.alshaya_algolia_index']['options']['algolia_index_list'] = '';
    $overrides['search_api.index.alshaya_algolia_index']['options']['algolia_index_batch_deletion'] = TRUE;

    $overrides['search_api.index.alshaya_algolia_product_list_index']['options']['algolia_index_batch_deletion'] = TRUE;
    $overrides['search_api.index.alshaya_algolia_product_list_index']['options']['object_id_field'] = 'field_skus';

    // Get index prefix for current environment.
    $index_prefix = $this->alshayaAlgoliaConfig->get('index_prefix');
    // Ensure we never connect to Index of another ENV.
    $overrides['search_api.index.alshaya_algolia_index']['options']['algolia_index_name'] = $index_prefix;
    $overrides['search_api.index.acquia_search_index']['options']['algolia_index_name'] = $index_prefix;
    // Algolia Index name will be like 01live_bbwae_product_list.
    $overrides['search_api.index.alshaya_algolia_product_list_index']['options']['algolia_index_name'] = $index_prefix . '_product_list';

    // This will need to be overridden in brand specific settings files on each
    // env using prod app for each brand.
    $algolia_env = $this->getAlgoliaEnv();
    $algolia_settings = Settings::get('algolia_sandbox.settings');
    if (!in_array($algolia_env, ['01test', '01uat', '01pprod', '01live'])) {
      $overrides['search_api.server.algolia']['backend_config']['application_id'] = $algolia_settings['app_id'];
      $overrides['search_api.server.algolia']['backend_config']['api_key'] = $algolia_settings['write_api_key'];
      $overrides['alshaya_algolia_react.settings']['application_id'] = $algolia_settings['app_id'];
      $overrides['alshaya_algolia_react.settings']['search_api_key'] = $algolia_settings['search_api_key'];
    }

    if (!$this->isIndexingFromDrupal()) {
      $overrides['search_api.index.alshaya_algolia_index']['read_only'] = TRUE;
      $overrides['search_api.index.alshaya_algolia_product_list_index']['read_only'] = TRUE;
    }

    return $overrides;
  }

  /**
   * Returns if indexing happens from Drupal or not.
   *
   * @return bool
   *   Returns true if indexing happens from Drupal else false if it happens
   *   from Magento.
   */
  private function isIndexingFromDrupal() {
    static $val;
    if (isset($val)) {
      return $val;
    }
    $val = $this->alshayaAlgoliaConfig->get('index_from_drupal');
    return $val;
  }

  /**
   * Gets the Algolia env.
   *
   * @return string
   *   Algoia env.
   */
  private function getAlgoliaEnv() {
    static $algolia_env;
    if (isset($algolia_env)) {
      return $algolia_env;
    }

    if ($this->isIndexingFromDrupal()) {
      $algolia_env = Settings::get('env');
      // Use local env for travis.
      $algolia_env = $algolia_env === 'travis' ? 'local' : $algolia_env;
    }
    else {
      $algolia_env = Settings::get('algolia_env');
    }
    return $algolia_env;
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
