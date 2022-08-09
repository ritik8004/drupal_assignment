<?php
// phpcs:ignoreFile

/**
 * @file
 *
 * Invalidate Varnish cache for specific URL directly.
 *
 * Usage: php tests/purge/ban.php www.mothercare.com.kw ar
 *
 * Above command will purge cache for the relative page /ar which is
 * Arabic home page.
 *
 * Please update list of balancers if required. You can find them on Acquia
 * Cloud. Ref: https://www.dropbox.com/s/w1y5tuw2vyikmt1/balancers.png?dl=0
 */

$domain = $argv[1] ?? '';
$url = $argv[2] ?? '';

if (empty($domain) || empty($url)) {
  print 'Domain and relative url are required' . PHP_EOL;
  print 'Usage: php tests/purge/ban.php www.mothercare.com.kw ar' . PHP_EOL;
  print 'Above command will purge cache for the relative page /ar which is Arabic home page.' . PHP_EOL;
  print PHP_EOL . PHP_EOL . PHP_EOL;
  die();
}

$balancers = [
  'bal-1495',
  'bal-1496',
  'bal-2295',
  'bal-2296',
  'bal-2979',
  'bal-2980',
  'bal-4807',
  'bal-4808',
  'bal-6559',
  'bal-6560',
  'bal-6561',
  'bal-6562',
];

foreach ($balancers as $balancer) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'http://' . $balancer . '.enterprise-g1.hosting.acquia.com/' . $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'BAN');


  $headers = [];
  $headers[] = 'X-Acquia-Purge: alshaya';
  $headers[] = 'Host: ' . $domain;
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    print 'Error:' . curl_error($ch) . PHP_EOL . PHP_EOL;
  }
  curl_close($ch);
}
