<?php
// @codingStandardsIgnoreFile

require_once 'common.php';

$urls_csv = array_map('str_getcsv', file(__DIR__ . '/only_urls.csv'));
$domains_csv = array_map('str_getcsv', file(__DIR__ . '/domains.csv'));

if (empty($urls_csv) || empty($domains_csv)) {
  print 'CSV files are either empty or not created.' . PHP_EOL;
  die();
}

$complete_url_list = [];
$ban = __DIR__ . '/../../tests/purge/ban.php';

foreach ($domains_csv as $domain) {
  $complete_url_list = [];
  foreach ($urls_csv as $url) {
    $complete_url_list[] = 'https://' . $domain[0] . $url[0];

    // Clearing varnish cache.
    $url = ltrim($url[0], '/');
    $command = "php $ban $domain[0] $url";
    print 'Running... ' . $command . PHP_EOL;
    shell_exec($command);
  }

  // Purging from cloudflare.
  $zone = get_zone_for_domain($domain[0]);
  if ($zone) {
    print 'Purging from Cloudflare...' . PHP_EOL;
    print_r(clear_cache_for_urls($zone, $complete_url_list));
  }
  else {
    print 'Error occurred';
  }
  print PHP_EOL;
}
