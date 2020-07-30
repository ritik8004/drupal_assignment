<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook to set index name for algolia.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

global $_acsf_site_name;
global $acsf_site_code;

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

/**
 * Return the Algolia keys for the specified brand.
 *
 * To note: These keys are for the Sanbox(dev) app for each brand.
 * For the test/uat/pprod/live envs, we use the prod Application and we add
 * the settings for those applications on ~/settings/settings-brandcode.php
 * on the server for each brand.
 *
 * @param string $site_code
 *   The site code. Eg: pbk, hm, mc, etc.
 *
 * @return array
 *   The Algolia app keys for the brand.
 */
function get_algolia_sandbox_brand_key_mapping(string $site_code) {
  // @todo Remove the 'default' once when sandbox app keys have been added for
  // all the brands.
  $mapping = [
    'default' => [
      'app_id' => 'testing24192T8KHZ',
      'write_api_key' => '1a3473b08a7e58f0b808fe4266e08187',
      'search_api_key' => '950ad607b0d79914702c82849af9a63f',
    ],
    'pbk' => [
      'app_id' => 'KBYTOTQY6T',
      'write_api_key' => 'bc6a377733b1f8812c094d709580faa6',
      'search_api_key' => '3f0b012a52119eb8e95b7ec359d3e881',
    ],
  ];

  return $mapping[$site_code] ?? $mapping['default'];
}

// This will need to be overridden in brand specific settings files on each
// env using prod app for each brand.
if (!in_array($algolia_env, ['01test', '01uat', '01pprod', '01live'])) {
  $algolia_settings = get_algolia_sandbox_brand_key_mapping($acsf_site_code);
  $config['search_api.server.algolia']['backend_config']['application_id'] = $algolia_settings['app_id'];
  $config['search_api.server.algolia']['backend_config']['api_key'] = $algolia_settings['write_api_key'];
  $config['block.block.alshayaalgoliareactautocomplete']['settings']['application_id'] = $algolia_settings['app_id'];
  $config['block.block.alshayaalgoliareactautocomplete']['settings']['search_api_key'] = $algolia_settings['search_api_key'];
}
