<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$appointment_settings = [];

if ($site_country_code && $site_country_code['site_code'] === 'bp') {
  $appointment_settings = [
    'headerNS' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
    'passwordNS' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText',
    'username' => '',
    'password' => '',
  ];
}

$settings['appointment_settings'] = $appointment_settings;
