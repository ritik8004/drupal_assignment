<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Get site environment.
$env = alshaya_get_site_environment();
$appointment_settings = [];

if (!empty($site_country_code) && $site_country_code['site_code'] === 'bp') {
  $appointment_settings = [
    'username' => '',
    'password' => '',
    'location_group_ext_id' => 'Boots',
    'timetrade_api_base_url' => 'https://api-stage.timetradesystems.co.uk',
    'timetrade_translation_base_url' => 'https://staging-translation.account.services'
  ];
  if (preg_match('/\d{2}(live|update)/', $env)) {
    // @TODO: Add 'timetrade_api_base_url' once we have it for prod.
    $appointment_settings['timetrade_api_base_url'] = '';
    $appointment_settings['timetrade_translation_base_url'] = 'https://translation.account.services';
  }
}

$settings['appointment_settings'] = $appointment_settings;
$settings['middleware_auth'] = '5um6y5nxl3oqms9qw0jai36qkryrrocg';
