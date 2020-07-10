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
  $appointment_settings = [
    'username' => 'bootsapiuser',
    'password' => 'jG4@dF0p',
    'location_group_ext_id' => 'Boots'
  ];
}

$settings['appointment_settings'] = $appointment_settings;
$settings['middleware_auth'] = '5um6y5nxl3oqms9qw0jai36qkryrrocg';
