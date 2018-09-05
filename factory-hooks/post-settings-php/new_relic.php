<?php
/**
 * @file
 * Implementation of New Relic.
 *
 * @see https://docs.acquia.com/articles/using-new-relic-monitoring-multisite-environment
 */

if (extension_loaded('newrelic')) {
  global $site_name;
  $is_amp_page = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '?amp') !== false;
  if ($is_amp_page) {
    // Disable newrelic for amp pages.
    newrelic_disable_autorum();
  }
  else {
    newrelic_set_appname("$site_name;alshaya.01live", '', 'true');
  }
}
