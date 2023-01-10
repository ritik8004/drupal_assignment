<?php
// phpcs:ignoreFile

/**
 * Script to purge queue for given env, brand and country.
 *
 * ==> php ./tests/apis/conductor_v2/purgeQueues.php live hm all
 * should purge the queue for all the country sites for HM brand on live ENV
 *
 * ==> php ./tests/apis/conductor_v2/purgeQueues.php live hm kw
 * should purge the queue for KW country site of HM brand on live ENV
 *
 * ==>  php ./tests/apis/conductor_v2/purgeQueues.php live all kw
 * should purge the queue for KW country for all the brands on live ENV
 */

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/variables.php';
require_once __DIR__ . '/../../../factory-hooks/environments/conductor.php';

if ((is_countable($argv) ? count($argv) : 0) < 4) {
  echo '=> Please enter all the arguments required.';
  echo PHP_EOL . '=> {env} {brand / all} {country / all}';
  echo PHP_EOL;
  die();
}

$env = isset($argv, $argv[1]) ? str_replace('01', '', $argv[1]) : '';
$brand = isset($argv, $argv[2]) ? $argv[2] : '';
$country = isset($argv, $argv[3]) ? $argv[3] : '';

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
    purge_queue_call($value['site_id'], [$env, $country_brand]);
  }
  else {
    $current_country = substr($country_brand, -2);
    $current_brand = substr($country_brand ,0,-2);

    if ($country == 'all' && $brand == $current_brand) {
      purge_queue_call($value['site_id'], [$env, $current_brand, $current_country]);
    }
    elseif ($brand == 'all' && $country == $current_country) {
      purge_queue_call($value['site_id'], [$env, $current_brand, $current_country]);
    }
    elseif ($brand == $current_brand && $country == $current_country) {
      purge_queue_call($value['site_id'], [$env, $current_brand, $current_country]);
    }
  }
}

function purge_queue_call($site_id, $args = []) {
  echo PHP_EOL . '=> Purging queue for site_id ==> ' . $site_id . ' ==> (' . json_encode($args) .')' . PHP_EOL;
  $data = purge_queue($site_id);
  if (get_object_vars($data)) {
    echo PHP_EOL .  '==> ' . json_encode($data);
  }
  echo PHP_EOL;
}
