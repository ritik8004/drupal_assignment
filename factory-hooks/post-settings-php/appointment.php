<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$appointment_settings = [];

if (!empty($site_country_code) && $site_country_code['site_code'] === 'bp') {
  // Get site environment.
  require_once DRUPAL_ROOT . '/../factory-hooks/environments/environments.php';
  $env = alshaya_get_site_environment();

  $appointment_settings = [
    'username' => 'bootsapiuser',
    'password' => 'jG4@dF0p',
    'location_group_ext_id' => 'Boots',
    'timetrade_api_base_url' => 'https://api-stage.timetradesystems.co.uk',
    'timetrade_translation_base_url' => 'https://staging-translation.account.services',
    'translation_api_key' => '',
    'project' => 'boots',
    'locations_to_skip' => 'alshayaadmin', // Comma separated for multiple.
    'numberOfSlots' => 500,
  ];
  if (preg_match('/\d{2}(live|update)/', $env)) {
    // @TODO: Add 'timetrade_api_base_url' once we have it for prod.
    $appointment_settings['timetrade_api_base_url'] = '';
    $appointment_settings['timetrade_translation_base_url'] = 'https://translation.account.services';
  }
}

$settings['appointment_settings'] = $appointment_settings;
