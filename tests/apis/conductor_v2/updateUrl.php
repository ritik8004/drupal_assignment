<?php
// phpcs:ignoreFile

/**
 * @file
 * Update URL for specific System.
 */

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/variables.php';
require_once __DIR__ . '/../../../factory-hooks/environments/mapping.php';

$system_id = (int) $argv[1] ?? '';
$find = $argv[2] ?? '';
$replace = $argv[3] ?? '';

if (empty($system_id) || empty($find) || empty($replace)) {
  print PHP_EOL;
  print 'Usage: php ./tests/apis/conductor_v2/updateUrl.php SYSTEM_ID FIND REPLACE';
  print 'Exmample: php ./tests/apis/conductor_v2/updateUrl.php 273 "hm-uat." "hm-uat2."';
}

// Create Drupal system for $country_code + $lang_code.
$drupal_system = (array) get_system($system_id);
print 'Existing:' . PHP_EOL;
print_r($drupal_system);

$drupal_system['url'] = str_replace($find, $replace, $drupal_system['url']);
print 'Updated:' . PHP_EOL;
print_r($drupal_system);

$response = update_system($drupal_system);
print PHP_EOL;
print_r($response);

print PHP_EOL;
