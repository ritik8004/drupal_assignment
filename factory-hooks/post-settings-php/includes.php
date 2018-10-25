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

// This variable is declared and filled in post-sites-php/includes.php
global $acsf_site_name;

global $acsf_site_code;

// If we are on local environment, the site name has not been detected yet.
if (empty($acsf_site_name) && $settings['env'] == 'local') {
  // Get site code from site uri.
  if (!empty($_SERVER['HTTP_HOST'])) {
    $hostname_parts = explode('.', $_SERVER['HTTP_HOST']);
    $host_site_code = str_replace('alshaya-', '', $hostname_parts[1]);
  }
  else {
    foreach ($_SERVER['argv'] as $arg) {
      preg_match('/[\\S|\\s|\\d|\\D]*local.alshaya-(\\S*).com/', $arg, $matches);
      if (!empty($matches)) {
        $host_site_code = $matches[1];
        break;
      }
    }
  }

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

// We merge the entire settings with the specific ones.
include_once DRUPAL_ROOT . '/../factory-hooks/environments/includes.php';
$settings = array_replace_recursive($settings, alshaya_get_specific_settings($acsf_site_code, $country_code, $settings['env']));
