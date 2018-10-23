<?php
/**
 * @file
 * Implementation of Loggly.
 *
 * @see https://www.loggly.com/blog/logs-for-drupal-why-you-need-them-and-how-to-do-it/
 */

// Do it for all envs except local for now.
if ($settings['env'] !== 'local') {
  global $site_name;

  $config['logs_http.settings']['severity_level'] = 6;
  $config['logs_http.settings']['uuid'] = $settings['env'] . '-' . $site_name;
  $config['logs_http.settings']['url'] = 'http://logs-01.loggly.com/inputs/dfab01a8-9cf2-423e-a1af-d6aa5ebe37a0/tag/http/';
}
