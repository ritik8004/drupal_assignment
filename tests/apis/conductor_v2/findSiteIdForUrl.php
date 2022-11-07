<?php
// phpcs:ignoreFile

/**
 * @file
 * Update URL for specific System.
 */

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/variables.php';
require_once __DIR__ . '/../../../factory-hooks/environments/mapping.php';

$find = $argv[1] ?? '';

if (empty($find)) {
  print PHP_EOL;
  print 'Usage: php ./tests/apis/conductor_v2/findSiteIdForUrl.php DOMAIN';
  print 'Exmample for Drupal: php ./tests/apis/conductor_v2/findSiteIdForUrl.php wekw-dev';
  print 'Exmample for MDC: php ./tests/apis/conductor_v2/findSiteIdForUrl.php wes-uat';
}

print PHP_EOL;

$not_found_counter = 0;

for ($i = 15; $i < 2500; $i++) {
  $system = (array) get_system($i);

  if (empty($system)) {
    $not_found_counter++;
  }
  else {
    $not_found_counter = 0;
    if (str_contains($system['url'], $find)) {
      print_r($system);
      continue;
    }
  }

  if ($not_found_counter > 50) {
    print "Tried till $i, no system found for last $not_found_counter checks.";
    break;
  }
}

print PHP_EOL;
