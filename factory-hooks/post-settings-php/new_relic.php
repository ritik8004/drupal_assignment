<?php
/**
 * @file
 * Implementation of New Relic.
 *
 * @see https://docs.acquia.com/articles/using-new-relic-monitoring-multisite-environment
 */
if (extension_loaded('newrelic')) {
  $domain_fragments = explode('.', $_SERVER['HTTP_HOST']);
  $site_name = array_shift($domain_fragments);
  newrelic_set_appname("$site_name;alshaya.01live", '', 'true');
}
