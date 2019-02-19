<?php

/**
 * Utility script to delete backups site backup taken via ACSF UI.
 * Available backups are listed here:
 * https://www.<env>-alshaya.acsitefactory.com/site-archive-list
 * Usage: php scripts/utilities/backup-cleaning.php <env>
 */

global $username;
$username = 'YOU_USERNAME';
global $api_key;
$api_key = 'YOUR_API_KEY';


$env = $argv[1];
if (!in_array($env, ['dev', 'dev2', 'dev3', 'test', 'qa2', 'uat', 'pprod'])) {
  return 'Invalid environment.';
}

$site_id_limit = 601;
$backup_day_old_limit = 60;

function invokeAPI($url, $data = [], $method = 'GET') {
  global $username;
  global $api_key;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $api_key);
  if (!empty($data)) {
    curl_setopt($ch, CURLOPT_POST, count($data));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
  }
  if ($method != 'GET') {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  }
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

  $result = json_decode(curl_exec($ch));
  curl_close($ch);

  return $result;
}

$base_url = 'https://www.';
if ($env !== 'prod') {
  $base_url .= $env . '-';
}
$base_url .= 'alshaya.acsitefactory.com/api/v1/';

// We can't get the site IDs from the API as there may be backups for sites
// which don't exists anymore. We simply loop through all possible IDs (inc.
// is +5 on ACSF).
for ($site_id = 101; $site_id <= $site_id_limit; $site_id+=5) {
  $url = $base_url . 'sites/' . $site_id . '/backups?limit=100';

  $backups = invokeAPI($url);

  foreach ($backups->backups ?? [] as $backup) {
    if ($backup->timestamp <= (time() - ((24 * 3600) * $backup_day_old_limit))) {
      $url = $base_url . 'sites/' . $site_id . '/backups/' . $backup->id;
      $res = invokeAPI($url, [], 'DELETE');

      echo 'We will delete backup ' . $backup->id . ' for site ' . $site_id . ' from ' . date('d-m-Y', $backup->timestamp) . ' : task id >> ' . $res->task_id . "\n";
    }
  }
}
