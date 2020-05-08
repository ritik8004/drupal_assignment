<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook to allow overriding settings.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

global $_acsf_site_name;
$acsf_site_code = substr($_acsf_site_name, 0, -2);
$country_code = substr($_acsf_site_name, -2);

// Allow overriding settings and config to set secret info directly from
// include files on server which can be per brand or brand country combination.
$settings_path = $settings['server_home_dir'] . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'settings-';

$brand_country_file = $settings_path . $acsf_site_code . $country_code . '.php';
if (file_exists($brand_country_file)) {
  include_once $brand_country_file;
}

$brand_file = $settings_path . $acsf_site_code . '.php';

if (file_exists($brand_file)) {
  include_once $brand_file;
}
