<?php

/**
 * @file
 * Utility file.
 */

require_once 'api-v2-auth.php';

$env = $argv[1] ?? '';

if (empty($env)) {
  print 'Please specify environment uuid.';
  exit;
}

$url = "environments/$env/crons";
try {
  $res = invokeApi($url);
}
catch (\Exception) {
  print 'Failed to get crons.';
  exit;
}

$res = json_decode($res, TRUE);
foreach ($res['_embedded']['items'] as $cron) {
  print_r($cron);
}
