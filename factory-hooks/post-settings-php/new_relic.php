<?php
/**
 * @file
 * Implementation of New Relic.
 *
 * @see https://docs.acquia.com/articles/using-new-relic-monitoring-multisite-environment
 */

if (extension_loaded('newrelic')) {
  global $acsf_site_name;
  newrelic_set_appname("$acsf_site_name;alshaya.01live", '', 'true');
}
