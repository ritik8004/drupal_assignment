<?php
// phpcs:ignoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook to set application for TrackJS.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

global $_acsf_site_name;

$config['track_js.settings']['application'] = implode('-', [
  $_acsf_site_name,
  $settings['env_name'],
]);

$config['datadog_js.settings']['application'] = 'datadoghq.eu';

// Token for all the non-prod envs is added below.
// We will need to set the same config on production with proper value.
// This is anyways safe as it is public token visible in browser too.
if ($settings['env_name'] !== 'live') {
  $config['datadog_js.settings']['token'] = 'pub9fea1ea3ed8eafec1fcd04e4a7eb2921';
}
