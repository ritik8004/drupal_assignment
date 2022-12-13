<?php
// phpcs:ignoreFile
/**
 * Script to get queue status for given env, brand.
 *
 * ==> php ./tests/apis/conductor_v2/getQueues.php
 * Gets the queue status for all the known sites in live env
 *
 * ==> php ./tests/apis/conductor_v2/getQueues.php uat
 * Gets the queue status for all the known sites in uat env
 *
 * ==> php ./tests/apis/conductor_v2/getQueues.php live hm
 * Gets the queue status for all the known sites in live env for hm brand
 */

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/variables.php';

require_once __DIR__ . '/../../../factory-hooks/environments/conductor.php';

$env = isset($argv, $argv[1]) ? str_replace('01', '', strtolower($argv[1])) : 'live';
$brand = isset($argv, $argv[2]) ? $argv[2] : '';

print PHP_EOL;
print '=> Trying to get queue sizes from ENV: ' . $env;
print PHP_EOL . PHP_EOL;

$env_map = [
  'live' => 'prod',
  'test' => 'qa',
];

$env = $env_map[$env] ?? $env;

while (1) {
  foreach ($conductors as $key => $value) {
    [$country_brand, $base_env] = get_brand_country_and_env($key);
    if ($base_env !== $env) {
      continue;
    }

    if (!empty($brand) && $brand !== substr($country_brand, 0, -2)) {
      continue;
    }

    $data = get_queue_total($value['site_id']);
    echo PHP_EOL . '=> ' . $country_brand . ' : ' . $data->total;
    print PHP_EOL;
  }

  sleep(30);
}
