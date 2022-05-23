<?php

namespace Drupal\alshaya_search_algolia;

use Drupal\Core\Cache\CacheableMetadata;
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
   * AlgoliaSearchIndexNameConfigOverrider constructor.
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

    if (!empty($overrides)) {
      return $overrides;
    }

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

    // @codingStandardsIgnoreLine
    global $_acsf_site_name;

    $algolia_env = Settings::get('env');
    // Use local env for travis.
    $algolia_env = $algolia_env === 'travis' ? 'local' : $algolia_env;

    $algolia_settings = Settings::get('algolia_sandbox.settings');

    // We want to use Algolia index name with 01 prefix all the time.
    $env_number = substr($algolia_env, 0, 2);
    if (is_numeric($env_number) && $env_number !== '01') {
      $algolia_env = '01' . substr($algolia_env, 2);
    }

    // During the production deployment, `01update` env is used and that is
    // not a valid index name prefix, we want to use `01live` only even there.
    if ($algolia_env == '01update') {
      $algolia_env = '01live';
    }
    // For non-prod env, we have env likes `01dev3up`, `01qaup` which are used
    // during release/deployment. We just remove last `up` from the env name
    // to use the original env. For example - original env for `01dev3up` will
    // be `01dev3`.
    elseif (substr($algolia_env, -2) == 'up') {
      $algolia_env = substr($algolia_env, 0, -2);
    }

    // Ensure we never connect to Index of another ENV.
    $overrides['search_api.index.alshaya_algolia_index']['options']['algolia_index_name'] = $algolia_env . '_' . $_acsf_site_name;
    $overrides['search_api.index.acquia_search_index']['options']['algolia_index_name'] = $algolia_env . '_' . $_acsf_site_name;
    // Algolia Index name will be like 01live_bbwae_product_list.
    $overrides['search_api.index.alshaya_algolia_product_list_index']['options']['algolia_index_name'] = $algolia_env . '_' . $_acsf_site_name . '_product_list';

    // This will need to be overridden in brand specific settings files on each
    // env using prod app for each brand.
    if (!in_array($algolia_env, ['01test', '01uat', '01pprod', '01live'])) {
      $overrides['search_api.server.algolia']['backend_config']['application_id'] = $algolia_settings['app_id'];
      $overrides['search_api.server.algolia']['backend_config']['api_key'] = $algolia_settings['write_api_key'];
      $overrides['alshaya_algolia_react.settings']['application_id'] = $algolia_settings['app_id'];
      $overrides['alshaya_algolia_react.settings']['search_api_key'] = $algolia_settings['search_api_key'];
    }

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
