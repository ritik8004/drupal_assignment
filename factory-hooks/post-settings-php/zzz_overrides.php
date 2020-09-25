<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook to allow overriding settings.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

use Drupal\Core\Serialization\Yaml;

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

$settings_path = $home . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'settings';

// Allow overriding settings to set data directly from YAML files on server
// which can be for stack or per brand or brand country combination.
$stack_file = $settings_path . '.yml';
$overridden_settings = [];
if (file_exists($stack_file)) {
  $overridden_settings = Yaml::decode(file_get_contents($stack_file));
}

$brand_country_file = $settings_path . '-' . $acsf_site_code . $country_code . '.yml';
if (file_exists($brand_country_file)) {
  $overridden_settings = array_merge($overridden_settings, Yaml::decode(file_get_contents($brand_country_file)));
}

$brand_file = $settings_path . '-' . $acsf_site_code . '.yml';
if (file_exists($brand_file)) {
  $overridden_settings = array_merge($overridden_settings, Yaml::decode(file_get_contents($brand_file)));
}

$settings = (!empty($overridden_settings))
  ? array_replace_recursive($settings, $overridden_settings)
  : $settings;

// Allow overriding settings and config to set secret info directly from
// include files on server which can be per brand or brand country combination.
$stack_file = $settings_path . '.php';
if (file_exists($stack_file)) {
  include_once $stack_file;
}

$brand_country_file = $settings_path . '-' . $acsf_site_code . $country_code . '.php';
if (file_exists($brand_country_file)) {
  include_once $brand_country_file;
}

$brand_file = $settings_path . '-' . $acsf_site_code . '.php';
if (file_exists($brand_file)) {
  include_once $brand_file;
}
