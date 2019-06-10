<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook to allow overriding settings.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

global $acsf_site_name;
$acsf_site_code = substr($acsf_site_name, 0, -2);
$country_code = substr($acsf_site_name, -2);

$home = ($settings['env'] == 'local') ? '/home/vagrant' : $_SERVER['HOME'];

// Allow overriding settings and config to set secret info directly from
// include files on server which can be per brand or brand country combination.
$settings_path = $home . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'settings-';

$brand_country_file = $settings_path . $acsf_site_code . $country_code . '.php';
if (file_exists($brand_country_file)) {
  include_once $brand_country_file;
}

$brand_file = $settings_path . $acsf_site_code . '.php';

if (file_exists($brand_file)) {
  include_once $brand_file;
}
