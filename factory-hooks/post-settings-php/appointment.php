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
    'location_group_ext_id' => 'Boots',
    'timetrade_api_base_url' => 'https://api-stage.timetradesystems.co.uk',
    'timetrade_translation_base_url' => 'https://translation.account.services',
    'translation_api_key' => '',
    'project' => 'boots',
    'locations_to_skip' => 'alshayaadmin', // Comma separated for multiple.
  ];
  if (preg_match('/\d{2}(live|update)/', $env)) {
    $appointment_settings['timetrade_api_base_url'] = 'https://api.timetradesystems.co.uk';
  }
}

$settings['appointment_settings'] = $appointment_settings;
