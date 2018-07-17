<?php

/**
 * @file
 * Example implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

use Symfony\Component\Yaml\Yaml;

// This variable is declared and filled in post-sites-php/includes.php
global $site_name;

global $site_code;

// If we are on local environment, the site name has not been detected yet.
if (empty($site_name) && $settings['env'] == 'local') {
  $data = Yaml::parse(file_get_contents(DRUPAL_ROOT . '/../blt/project.local.yml'));

  foreach ($data['sites'] as $site_code => $site_info) {
    $site_alias = 'local.alshaya-' . $site_code . '.com';
    if ($_SERVER['HTTP_HOST'] == $site_alias) {
      $site_name = $site_code;
      break;
    }
  }

  if (empty($site_name)) {
    print 'Invalid domain';
    die();
  }
}

$site_code = substr($site_name, 0, -2);
$country_code = substr($site_name, -2);

// Calculate country code for current site name.
// Country code is based on ISO 3166-1 alpha-2.
$settings['country_code'] = strtoupper($country_code);

// We merge the entire settings with the specific ones.
include_once DRUPAL_ROOT . '/../factory-hooks/environments/includes.php';
$settings = array_replace_recursive($settings, alshaya_get_specific_settings($site_code, $country_code, $settings['env']));
