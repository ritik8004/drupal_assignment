<?php

/**
 * @file
 * Implementation of New Relic.
 *
 * @see https://docs.acquia.com/articles/using-new-relic-monitoring-multisite-environment
 */

if (extension_loaded('newrelic')) {
  $env = 'local';

  if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
    $env = $_ENV['AH_SITE_ENVIRONMENT'];
  }
  elseif (getenv('TRAVIS')) {
    $env = 'travis';
  }

  global $_acsf_site_name;
  newrelic_set_appname("$_acsf_site_name;alshaya.$env", '', 'true');
  // Disable newrelic for all pages.
  newrelic_disable_autorum();
}
