<?php

/**
 * @file
 * PHP script to get the all the webservers of a given environment.
 */

require_once 'api-v2-auth.php';

$env = $argv[1] ?? '';

if (empty($env)) {
  print 'Please specify environment uuid.';
  exit;
}

$url = "environments/$env/servers";
try {
  $res = invokeApi($url);
}
catch (\Exception) {
  print 'Failed to get web servers.';
  exit;
}

$res = json_decode($res, TRUE);
foreach ($res['_embedded']['items'] as $server) {
  // Only web servers.
  if (!in_array('web', $server['roles'])) {
    continue;
  }

  echo $server['hostname'] . '
';
}
