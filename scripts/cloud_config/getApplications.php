<?php

/**
 * @file
 * Utility file.
 */

require_once 'api-v2-auth.php';

$url = 'applications';
try {
  $res = invokeApi($url);
}
catch (\Exception) {
  print 'Failed to get applications';
  exit;
}

$res = json_decode($res, TRUE, 512, JSON_THROW_ON_ERROR);
foreach ($res['_embedded']['items'] as $app) {
  echo $app['uuid'] . ' ' . $app['name'] . '
';
}
