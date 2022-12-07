<?php
// @codingStandardsIgnoreFile

require_once 'common.php';

$domain = $argv[1] ?? '';

if (empty($domain)) {
  print 'Please specify the domain to clear cache for.';
  print PHP_EOL;
  exit;
}

$zone = get_zone_for_domain($domain);

if (empty($zone)) {
  print PHP_EOL;
  die();
}


$rules = get_page_rules_for_zone($zone);
print_r($rules);

print PHP_EOL;
