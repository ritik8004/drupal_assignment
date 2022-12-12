<?php
// @codingStandardsIgnoreFile

require_once 'common.php';

// CSV file example urls.csv.sample
// SUMO query
// _sourceCategory=syslog namespace=alshaya*live.apache-access .jpg
//| where status_code = 200
//| where bytes_sent = "-"
//| where method not in ("HEAD", "OPTIONS")
//| concat("https://", apache_host, url) as final_url
//| fields apache_host, final_url
//| count by apache_host, final_url
$csv = array_map('str_getcsv', file(__DIR__ . '/urls.csv'));

if (empty($csv)) {
  print 'CSV file is either empty or not created.' . PHP_EOL;
  die();
}

$data_by_domain = [];

$ban = __DIR__ . '/../../tests/purge/ban.php';

foreach ($csv as $row) {
  $data_by_domain[$row[0]][] = $row[1];

  $url = trim(explode($row[0], $row[1])[1], '/');
  $command = "php ${ban} ${row[0]} ${url}";
  shell_exec($command);
}

foreach ($data_by_domain as $domain => $urls) {
  $zone = get_zone_for_domain($domain);

  if ($zone) {
    print_r(clear_cache_for_urls($zone, $urls));
  }
  else {
    print 'Error occurred';
  }

  print PHP_EOL;
}
