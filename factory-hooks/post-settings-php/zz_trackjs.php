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
