<?php
// phpcs:ignoreFile

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

  if (getenv('LANDO')) {
    $home = '/app/local_home';
  }
  elseif (getenv('IS_DDEV_PROJECT')) {
    $home = '/var/www/html/local_home';
  }

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
$settings_path = $home . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'settings';

$stack_file = $settings_path . '.php';
if (empty($settings['alshaya_override_stack_file_included']) && file_exists($stack_file)) {
  include $stack_file;
  $settings['alshaya_override_stack_file_included'] = TRUE;
}

$brand_country_file = $settings_path . '-' . $acsf_site_code . $country_code . '.php';
if (file_exists($brand_country_file)) {
  include_once $brand_country_file;
}

$brand_file = $settings_path . '-' . $acsf_site_code . '.php';
if (file_exists($brand_file)) {
  include_once $brand_file;
}

$settings ??= [];
$settings['acsf_site_code'] = $acsf_site_code;
// Allow overriding settings and config to set secret info directly from
// include files on server which can be for stack or per brand or brand
// country combination.
$overridding_settings_files = [
  $settings_path,
  $settings_path . '-' . $acsf_site_code,
  $settings_path . '-' . $acsf_site_code . $country_code,
];

$extensions = ['yml', 'php'];

foreach ($extensions as $extension) {
  foreach ($overridding_settings_files as $file) {
    $file = $file . ".$extension";
    switch ($extension) {
      case 'yml':
        if (file_exists($file)) {
          $overridden_settings = Yaml::decode(file_get_contents($file));
          $settings = (!empty($overridden_settings))
            ? array_replace_recursive($settings, $overridden_settings)
            : $settings;
        }
        break;
      case 'php':
        if (file_exists($file)) {
          include_once $file;
        }
        break;
    }
  }
}

// Set css_js_query_string_for_compressed to TRUE.
$settings['css_js_query_string_for_compressed'] = TRUE;
