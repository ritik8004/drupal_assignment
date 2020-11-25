<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook to allow overriding settings.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

require_once DRUPAL_ROOT . '/../factory-hooks/environments/environments.php';
$env = alshaya_get_site_environment();

if ($env === 'local') {
  global $host_site_code;
  $home = '/home/vagrant';

  $site_country_code = alshaya_get_site_country_code($host_site_code);
}
else {
  global $_acsf_site_name;
  $home = $_SERVER['HOME'];

  $site_country_code = alshaya_get_site_country_code($_acsf_site_name);
}

$acsf_site_code = $site_country_code['site_code'];
$country_code = $site_country_code['country_code'];

// Allow overriding settings and config to set secret info directly from
// include files on server which can be per brand or brand country combination.
$settings_path = $home . DIRECTORY_SEPARATOR . 'pre-settings' . DIRECTORY_SEPARATOR . 'pre-settings';

$brand_country_file = $settings_path . '-' . $acsf_site_code . $country_code . '.php';
if (file_exists($brand_country_file)) {
  include_once $brand_country_file;
}
