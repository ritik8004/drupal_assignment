<?php
// phpcs:ignoreFile

/**
 * Script to pause all the queues (non-prod) that have huge number of items.
 *
 * ==> php ./tests/apis/conductor_v2/pauseQueuesWithHugeCount.php
 */

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/variables.php';
require_once __DIR__ . '/../../../factory-hooks/environments/conductor.php';

echo PHP_EOL;

global $mode;
$mode = isset($argv, $argv[1]) ? $argv[1] : 'report';

$limit = isset($argv, $argv[2]) ? intval($argv[2]) : 0;
if (empty($limit)) {
  $limit = 250;
}

$env_map = [
  'prod' => 'live',
  'qa' => 'test',
];

foreach ($conductors as $key => $value) {
  if (empty($value['site_id'])) {
    continue;
  }

  [$country_brand, $base_env] = get_brand_country_and_env($key);
  $country = substr($country_brand, 0, strlen($country_brand) - 2);
  $brand = substr($country_brand, -2);

  $base_env = $env_map[$base_env] ?? $base_env;
  if ($base_env === 'prod' || $base_env === 'live') {
    continue;
  }

  $queue_count = get_queue_total($value['site_id']);
  if ($mode !== 'report') {
    echo '=> ' . $country_brand . ' : ' . $base_env . ' : ' . $queue_count->total;
    echo PHP_EOL;
  }
  if (is_object($queue_count) && isset($queue_count->total) && $queue_count->total > $limit) {
    update_queue_status_call($value['site_id'], [$base_env, $brand, $country]);
    if ($mode === 'report') {
      echo 'Queue paused for ' . $value['site_id'] . ' : ' . $country_brand . ' : ' . $base_env . '. Queue size: ' . $queue_count->total;
      echo PHP_EOL;
    }
  }
}

echo PHP_EOL;

function update_queue_status_call($site_id, $args = []) {
  global $mode;
  if ($mode !== 'report') {
    echo '=> Pausing queue for site_id ==> ' . $site_id . ' ==> (' . json_encode($args) .')';
    echo PHP_EOL;
  }
  $data = update_queue_status($site_id, TRUE);
  if ($mode !== 'report') {
    if (get_object_vars($data)) {
      echo '==> ' . json_encode($data);
      echo PHP_EOL;
    }
    echo PHP_EOL;
  }
}
