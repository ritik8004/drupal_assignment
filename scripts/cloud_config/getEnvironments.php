<?php

/**
 * @file
 * Utility: file.
 */

require_once 'api-v2-auth.php';

$app = $argv[1] ?? '';

if (empty($app)) {
  print 'Please specify application uuid.';
  exit;
}

$url = "applications/$app/environments";
try {
  $res = invokeApi($url);
}
catch (\Exception) {
  print 'Failed to get environments.';
  exit;
}

$res = json_decode($res, TRUE);
foreach ($res['_embedded']['items'] as $env) {
  echo $env['id'] . ' ' . $env['name'] . '
';
}
