<?php
// phpcs:ignoreFile

/**
 * Script to resume all the queues (non-prod) that have less number of items.
 *
 * ==> php ./tests/apis/conductor_v2/resumeQueuesWithLessCount.php
 */
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/variables.php';
require_once __DIR__ . '/../../../factory-hooks/environments/conductor.php';

global $mode;
$mode = isset($argv, $argv[1]) ? $argv[1] : 'report';

echo PHP_EOL;

$file_path = __DIR__ . '/queue_counts.csv';

$old = is_readable($file_path)
  ? array_map('str_getcsv', file($file_path))
  : [];

$old_data = [];
foreach ($old as $row) {
  $old_data[$row[0]] = [
    'count' => $row[3],
    'old_time' => $row[4],
  ];
}

$data = [];

$data[] = [
  'site_id',
  'country_brand',
  'env',
  'queue_count',
  'time',
];

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

  $queue_count = get_queue_total($value['site_id']);
  if (is_object($queue_count) && isset($queue_count->total)) {
    if (isset($old_data[$value['site_id']]) && $queue_count->total > 10) {
      $old_site_data = $old_data[$value['site_id']];

      $minutes_ago = ' ' . ceil((time() - $old_site_data['old_time']) / 60) . ' minutes ago';

      if ($old_site_data['count'] < $queue_count->total) {
        echo '++ Queue count increased for ' . $value['site_id'] . ' : ' . $country_brand . ' : ' . $base_env . ', Current count: ' . $queue_count->total . ', Old count: ' . $old_site_data['count'] . $minutes_ago;
        echo PHP_EOL;
      }
      elseif ($mode !== 'report') {
        if ($old_site_data['count'] == $queue_count->total) {
          echo '== Queue count stable for ' . $value['site_id'] . ' : ' . $country_brand . ' : ' . $base_env . '. Queue size: ' . $queue_count->total;
        }
        else {
          echo '-- Queue count decreased for ' . $value['site_id'] . ' : ' . $country_brand . ' : ' . $base_env . ', Current count: ' . $queue_count->total . ', Old count: ' . $old_site_data['count'] . $minutes_ago;
        }
        echo PHP_EOL;
      }
    }

    $data[] = [
      $value['site_id'],
      $country_brand,
      $base_env,
      $queue_count->total,
      time(),
    ];
  }
}

$output = '';
foreach ($data as $row) {
  $output .= implode(',', $row);
  $output .= PHP_EOL;
}

file_put_contents($file_path, $output);
