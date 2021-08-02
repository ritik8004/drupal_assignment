<?php
// @codingStandardsIgnoreFile

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

$config['datadog_js.settings']['token'] = 'pub9fea1ea3ed8eafec1fcd04e4a7eb2921';
$config['datadog_js.settings']['application'] = 'datadoghq.eu';
