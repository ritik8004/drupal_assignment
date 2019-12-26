<?php
// @codingStandardsIgnoreFile
/**
 * Script to get queue status for given env, brand.
 *
 * ==> php ./tests/apis/conductor_v2/processQueues.php
 * Gets the queue status for all the known sites in live env
 *
 * ==> php ./tests/apis/conductor_v2/processQueues.php uat
 * Gets the queue status for all the known sites in uat env
 *
 * ==> php ./tests/apis/conductor_v2/processQueues.php live hm
 * Gets the queue status for all the known sites in live env for hm brand
 */

const DRUPAL_ROOT = __DIR__ . '/../../';

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

foreach ($conductors as $key => $value) {
  list($country_brand, $base_env) = explode('_', $key);
  if ($base_env !== $env) {
    continue;
  }

  if (!empty($brand) && $brand !== substr($country_brand, 0, -2)) {
    continue;
  }

  $data = get_queue_total($value['site_id']);

  if ($data->total > 0) {
    print PHP_EOL . '=> Processing queue for a minute for ' . $country_brand . ' : ' . $data->total;

    $current_brand = substr($country_brand ,0, -2);
    $current_country = substr($country_brand, -2);

    // Resume.
    update_queue_status_call('resume', $value['site_id'], FALSE, [$env, $current_brand, $current_country]);
    sleep(min($data->total, 60));

    // Pause.
    update_queue_status_call('pause', $value['site_id'], TRUE, [$env, $current_brand, $current_country]);
    sleep(min($data->total, 120));
  }
  else {
    print PHP_EOL . '=> Not processing queue for ' . $country_brand . ' : ' . $data->total;
  }

  print PHP_EOL;
}

function update_queue_status_call($status_txt, $site_id, $status, $args = []) {
  echo PHP_EOL . '=> ' . $status_txt . ' queue for site_id ==> ' . $site_id . ' ==> (' . json_encode($args) .')';
  $data = update_queue_status($site_id, $status);
  if (get_object_vars($data)) {
    echo PHP_EOL .  '==> ' . json_encode($data);
  }
  echo PHP_EOL;
}
