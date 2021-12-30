<?php
// @codingStandardsIgnoreFile

/**
 * Example of execution:
 * php scripts/cloudflare/clear_prefix.php www.footlocker.ae/modules/react/
 */

require_once 'common.php';


$url = $argv[1] ?? '';
$domain = explode('/', $url)[0];

if (empty($domain)) {
  print 'Please specify the domain to clear cache for.';
  print PHP_EOL;
  exit;
}

$zone = get_zone_for_domain($domain);

if ($zone) {
  print_r(clear_cache_for_prefix($zone, $url));
}
else {
  print 'Error occurred';
}

print PHP_EOL;
