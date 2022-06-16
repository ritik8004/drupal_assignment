<?php

/**
 * @file
 * Contains common generic site related helper functions.
 */

/**
 * Get site environment.
 *
 * @return mixed|string
 *   Site Environment.
 */
function alshaya_get_site_environment() {
  $env = 'local';

  if (getenv('AH_SITE_GROUP') && getenv('AH_SITE_ENVIRONMENT')) {
    $env = getenv('AH_SITE_ENVIRONMENT');
  }
  elseif (getenv('TRAVIS') || getenv('CI') || getenv('GITHUB_ACTIONS')) {
    $env = 'travis';
  }

  return $env;
}

/**
 * Get site_code and country_code from site_name.
 *
 * @param string|null $site_name
 *   Site name in format like 'hmkw' or 'bbwae'.
 *
 * @return array
 *   Array of site and country code.
 */
function alshaya_get_site_country_code($site_name = '') {
  if (empty($site_name)) {
    // phpcs:ignore
    global $host_site_code, $_acsf_site_name;

    // Get host_site_code or acsf_site_name based on environment.
    $site_name = (alshaya_get_site_environment() === 'local')
      ? $host_site_code
      : $_acsf_site_name;
  }

  return [
    'site_code' => substr($site_name, 0, -2),
    'country_code' => substr($site_name, -2),
  ];
}
