<?php
// phpcs:ignoreFile

/**
 * Script to pause / resume queue for given env, brand and country.
 *
 * ==> php ./tests/apis/conductor_v2/pauseQueues.php live hm all pause
 * should pause the queue for all the country sites for HM brand on live ENV
 *
 * ==> php ./tests/apis/conductor_v2/pauseQueues.php live hm all resume
 * should resume the queue for all the country sites for HM brand on live ENV
 *
 * ==> php ./tests/apis/conductor_v2/pauseQueues.php live hm kw pause
 * should pause the queue for KW country site of HM brand on live ENV
 *
 * ==>  php ./tests/apis/conductor_v2/pauseQueues.php live all kw pause
 * should pause the queue for KW country for all the brands on live ENV
 */

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/variables.php';
require_once __DIR__ . '/../../../factory-hooks/environments/conductor.php';

if ((is_countable($argv) ? count($argv) : 0) < 5) {
  echo '=> Please enter all the arguments required.';
  echo PHP_EOL . '=> {env} {brand / all} {country / all} {status: pause / resume}';
  echo PHP_EOL;
  die();
}
elseif (!in_array($argv[4], ['pause', 'resume'])) {
  echo '=> Please enter pause/resume value for status argument.';
  echo PHP_EOL . '=> Required args: {env} {brand / all} {country / all} {status: pause / resume}';
  echo PHP_EOL;
  die();
}

$env = isset($argv, $argv[1]) ? str_replace('01', '', $argv[1]) : '';
$brand = isset($argv, $argv[2]) ? $argv[2] : '';
$country = isset($argv, $argv[3]) ? $argv[3] : '';
$status = isset($argv, $argv[4]) ? $argv[4] : '';

$status = ($status == 'pause');
$status_txt = ($status) ? 'pausing' : 'resuming';

$env_map = [
  'prod' => 'live',
  'qa' => 'test',
];

foreach ($conductors as $key => $value) {
  [$country_brand, $base_env] = get_brand_country_and_env($key);

  $base_env = $env_map[$base_env] ?? $base_env;

  if ($env !== $base_env || empty($value['site_id'])) {
    continue;
  }

  if ($country == 'all' && $brand == 'all' ) {
    update_queue_status_call($status_txt, $value['site_id'], $status, [$env, $country_brand]);
  }
  else {
    $current_country = substr($country_brand, -2);
    $current_brand = substr($country_brand ,0,-2);

    if ($country == 'all' && $brand == $current_brand) {
      update_queue_status_call($status_txt, $value['site_id'], $status, [$env, $current_brand, $current_country]);
    }
    elseif ($brand == 'all' && $country == $current_country) {
      update_queue_status_call($status_txt, $value['site_id'], $status, [$env, $current_brand, $current_country]);
    }
    elseif ($brand == $current_brand && $country == $current_country) {
      update_queue_status_call($status_txt, $value['site_id'], $status, [$env, $current_brand, $current_country]);
    }
  }
}

function update_queue_status_call($status_txt, $site_id, $status, $args = []) {
  echo PHP_EOL . '=>' . $status_txt . ' queue status for site_id ==> ' . $site_id . ' ==> (' . json_encode($args) .')';
  $data = update_queue_status($site_id, $status);
  if (get_object_vars($data)) {
    echo PHP_EOL .  '==> ' . json_encode($data);
  }
  echo PHP_EOL;
}
