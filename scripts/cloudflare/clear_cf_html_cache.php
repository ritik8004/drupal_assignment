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

if ($zone) {
  print_r(clear_cache_for_domain($zone, $domain));
}
else {
  print 'Error occurred';
}

print PHP_EOL;
