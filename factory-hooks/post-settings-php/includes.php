<?php
// @codingStandardsIgnoreFile

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
global $_acsf_site_name;

global $acsf_site_code;

// If we are on local environment, the site name has not been detected yet.
if (empty($_acsf_site_name) && $settings['env'] == 'local') {
  global $host_site_code;

  $data = Yaml::parse(file_get_contents(DRUPAL_ROOT . '/../blt/alshaya_local_sites.yml'));

  foreach ($data['sites'] as $acsf_site_code => $site_info) {
    if ($host_site_code == $acsf_site_code) {
      $_acsf_site_name = $acsf_site_code;
      break;
    }
  }

  // We don't want to interrupt script on default domain, otherwise drush command without --uri parameter would fail
  if ( (empty($_acsf_site_name)) && ($host_site_code != 'default_local') && ($host_site_code) ) {
    print 'Invalid domain';
    die();
  }

  // We hardcode vsae site for travis Drupal installation.
  // We must choose some site to test whether Drupal installation works
  // properly. But it doesn't really matter too much which site we will install
  // locally, as we only run very simplistic behat tests against it.
  if ($env == 'travis') {
    echo "Setting up vsae for travis environment.";
    $_acsf_site_name = 'vsae';
  }
}

$acsf_site_code = substr($_acsf_site_name, 0, -2);
$country_code = substr($_acsf_site_name, -2);

// Calculate country code for current site name.
// Country code is based on ISO 3166-1 alpha-2.
$settings['country_code'] = strtoupper($country_code);

// Filepath for MDC rabbitmq credentials.
$rabbitmq_creds_dir = $settings['server_home_dir'] . '/rabbitmq-creds/';
if ($settings['env'] === 'local') {
  $rabbitmq_creds_dir .= $settings['env'] . DIRECTORY_SEPARATOR;
}

$settings['alshaya_api.settings']['rabbitmq_credentials_directory'] = $rabbitmq_creds_dir;

// Avoid old & temporary tables in DB used while updating the entity.
$settings['entity_update_backup'] = FALSE;

// We merge the entire settings with the specific ones.
include_once DRUPAL_ROOT . '/../factory-hooks/environments/includes.php';
$settings = array_replace_recursive($settings, alshaya_get_specific_settings($acsf_site_code, $country_code, $settings['env_name']));
