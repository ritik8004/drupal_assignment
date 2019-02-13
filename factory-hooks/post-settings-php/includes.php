<?php

/**
 * @file
 * Example implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Set config directories to default location.
$config_directories['vcs'] = '../config/default';
$config_directories['sync'] = '../config/default';

use Symfony\Component\Yaml\Yaml;

global $env;

// This variable is declared and filled in post-sites-php/includes.php
global $acsf_site_name;

global $acsf_site_code;

// If we are on local environment, the site name has not been detected yet.
if (empty($acsf_site_name) && $settings['env'] == 'local') {
  global $host_site_code;

  $data = Yaml::parse(file_get_contents(DRUPAL_ROOT . '/../blt/alshaya_local_sites.yml'));

  foreach ($data['sites'] as $acsf_site_code => $site_info) {
    if ($host_site_code == $acsf_site_code) {
      $acsf_site_name = $acsf_site_code;
      break;
    }
  }

  // We don't want to interrupt script on default domain, otherwise drush command without --uri parameter would fail
  if ( (empty($acsf_site_name)) && ($host_site_code != 'default_local') ) {
    print 'Invalid domain';
    die();
  }
}

$acsf_site_code = substr($acsf_site_name, 0, -2);
$country_code = substr($acsf_site_name, -2);

// Calculate country code for current site name.
// Country code is based on ISO 3166-1 alpha-2.
$settings['country_code'] = strtoupper($country_code);

// Filepath for MDC rabbitmq credentials.
$rabbitmq_creds_dir = $env == 'local' ? '/home/vagrant/rabbitmq-creds/' : '/home/alshaya/rabbitmq-creds/' . $settings['env'] . '/';

$settings['alshaya_api.settings']['rabbitmq_credentials_directory'] = $rabbitmq_creds_dir;

// We merge the entire settings with the specific ones.
include_once DRUPAL_ROOT . '/../factory-hooks/environments/includes.php';
$settings = array_replace_recursive($settings, alshaya_get_specific_settings($acsf_site_code, $country_code, $settings['env']));

// Allow overriding settings and config to set secret info directly from
// include files on server which can be per brand or brand country combination.
$settings_path = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'settings-';

$brand_country_file = $settings_path . $acsf_site_code . $country_code . '.php';
if (file_exists($brand_country_file)) {
  include_once $brand_country_file;
}

$brand_file = $settings_path . $acsf_site_code . '.php';

if (file_exists($brand_file)) {
  include_once $brand_file;
}
