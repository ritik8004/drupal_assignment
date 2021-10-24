<?php

/**
 * @file
 * Implementation of New Relic.
 *
 * @see https://docs.acquia.com/articles/using-new-relic-monitoring-multisite-environment
 */

if (extension_loaded('newrelic')) {
  $env = 'local';

  $ah_env = getenv('AH_SITE_ENVIRONMENT');
  if ($ah_env && $ah_env !== 'ide') {
    $env = $ah_env;
  }
  elseif (getenv('TRAVIS') || getenv('CI_BUILD_ID')) {
    $env = 'travis';
  }

  global $_acsf_site_name;
  newrelic_set_appname("alshaya.$env.$_acsf_site_name;alshaya.$env", '', 'true');
  // Disable newrelic for all pages.
  newrelic_disable_autorum();
}
