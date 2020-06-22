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
    'username' => '',
    'password' => '',
    'location_group_ext_id' => 'Boots'
  ];
}

$settings['appointment_settings'] = $appointment_settings;
