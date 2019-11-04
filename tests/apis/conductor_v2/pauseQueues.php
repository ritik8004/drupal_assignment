<?php
// @codingStandardsIgnoreFile

const DRUPAL_ROOT = __DIR__ . '/../../';

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/variables.php';
require_once __DIR__ . '/../../../factory-hooks/environments/conductor.php';

if (count($argv) < 5) {
  echo '=> Please enter all the arguments required.';
  echo PHP_EOL . '=> {env} {brand / all} {country / all} {status: pause / resume}';
  echo PHP_EOL;
  die();
}

$env = isset($argv, $argv[1]) ? $argv[1] : '';
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
  list($country_brand, $base_env) = explode('_', $key);
  $base_env = $env_map[$base_env] ?? $base_env;

  if ($env !==  '01' . $base_env || empty($value['site_id'])) {
    continue;
  }

  if ($country == 'all' && $brand == 'all' ) {
    upate_queue_status_call($status_txt, $value['site_id'], $status, [$env, $country_brand]);
    continue;
  }
  else {
    $current_country = substr($country_brand, -2);
    $current_brand = substr_replace($country_brand ,"",-2);

    if ($country == 'all' && $brand == $current_brand) {
      upate_queue_status_call($status_txt, $value['site_id'], $status, [$env, $current_brand, $current_country]);
    }
    else if ($brand == 'all' && $country == $current_country) {
      upate_queue_status_call($status_txt, $value['site_id'], $status, [$env, $current_brand, $current_country]);
    }
    else if ($brand == $current_brand && $country == $current_country) {
      upate_queue_status_call($status_txt, $value['site_id'], $status, [$env, $current_brand, $current_country]);
    }
  }
}

function upate_queue_status_call($status_txt, $site_id, $status, $args = []) {
  echo PHP_EOL . '=>' . $status_txt . ' queue status for site_id ==> ' . $site_id . ' ==> (' . json_encode($args) .')';
  echo PHP_EOL;
  update_queue_status($site_id, $status);
}
