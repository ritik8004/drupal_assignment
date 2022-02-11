<?php
// @codingStandardsIgnoreFile

/**
 * Example of execution:
 * php scripts/cloudflare/clear_cf_specific_url.php www.bathandbodyworks.com.sa/modules/react/alshaya_algolia_react/dist/vendors~atb-4b8779bc4a39b2b56c2e.js
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
  print_r(clear_cache_for_url($zone, $url));
}
else {
  print 'Error occurred';
}

print PHP_EOL;
