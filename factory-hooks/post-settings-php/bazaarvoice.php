<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$bazaarvoice_settings = [];

// Get site environment.
require_once DRUPAL_ROOT . '/../factory-hooks/environments/environments.php';
$env = alshaya_get_site_environment();

$bazaarvoice_settings = [
  'bazaarvoice_api_base_url' => 'https://stg.api.bazaarvoice.com',
];
if (preg_match('/\d{2}(live|update)/', $env)) {
  $bazaarvoice_settings['bazaarvoice_api_base_url'] = 'https://api.bazaarvoice.com';
}

$settings['bazaarvoice_settings'] = $bazaarvoice_settings;
