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
if (substr($algolia_env, 0, 2) !== '01') {
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
$config['search_api.index.acquia_search_index']['options']['algolia_index_name'] = $algolia_env . '_' . $_acsf_site_name;
$config['block.block.autocompletewidgetofalgolia']['settings']['index'] = $algolia_env . '_' . $_acsf_site_name;

// Values for developer machine here. This will need to be overridden in brand
// specific settings files on each env for each brand.
$settings['search_api.server.algolia']['backend_config']['application_id'] = 'testing24192T8KHZ';
$settings['search_api.server.algolia']['backend_config']['api_key'] = '628e74a9b6f3817cdd868278c8b8656e';
$settings['block.block.autocompletewidgetofalgolia']['settings']['application_id'] = 'testing24192T8KHZ';
$settings['block.block.autocompletewidgetofalgolia']['settings']['search_api_key'] = 'afeb84ab13757e11fbe8765142e2d7ad';
