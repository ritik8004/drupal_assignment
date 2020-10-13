<?php

/**
 * @file
 * Config file.
 */

global $_clientId, $_clientSecret;

$_clientId = '';
$_clientSecret = '';

// If credentials not available, read/check from home directory.
if (empty($_clientId) || empty($_clientSecret)) {
  $home = getenv('HOME');
  include_once $home . '/acquia_cloud_api_creds.php';
}
