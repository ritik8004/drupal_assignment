<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook to set index name for algolia.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

global $_acsf_site_name;

$algolia_env = $settings['env'];

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
$config['search_api.index.alshaya_algolia_index']['options']['algolia_index_name'] = $algolia_env . '_' . $_acsf_site_name;
$config['search_api.index.acquia_search_index']['options']['algolia_index_name'] = $algolia_env . '_' . $_acsf_site_name;
// Algolia Index name will be like 01live_bbwae_product_list
$config['search_api.index.alshaya_algolia_product_list_index']['options']['algolia_index_name'] = $algolia_env . '_' . $_acsf_site_name . '_product_list';

// This will need to be overridden in brand specific settings files on each
// env using prod app for each brand.
if (!in_array($algolia_env, ['01test', '01uat', '01pprod', '01live'])) {
  $config['search_api.server.algolia']['backend_config']['application_id'] = $settings['algolia_sandbox.settings']['app_id'];
  $config['search_api.server.algolia']['backend_config']['api_key'] = $settings['algolia_sandbox.settings']['write_api_key'];
  $config['alshaya_algolia_react.settings']['application_id'] = $settings['algolia_sandbox.settings']['app_id'];
  $config['alshaya_algolia_react.settings']['search_api_key'] = $settings['algolia_sandbox.settings']['search_api_key'];
}
