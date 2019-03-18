<?php

/**
 * @file
 */

// Set the variable as false initially so
// that file cannot be accessed till server is verified.
$security_check = FALSE;

if (($map = gardens_site_data_load_file()) && isset($map['sites'])) {
  foreach ($map['sites'] as $domain => $site_details) {
    // Match domain names with the site in SERVER.
    if ($_SERVER['SERVER_NAME'] == $domain) {
      $security_check = TRUE;
      break;
    }
  }
}

if ($security_check) {
  header('Content-Type: application/json');
  $url = 'https://' . $_SERVER['SERVER_NAME'] . '/en/well-known/assetlinks.json';
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($curl, CURLOPT_URL, $url);
  print curl_exec($curl);
}
