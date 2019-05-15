<?php

/**
 * @file
 */

include_once '../sites/g/sites.inc';

if (($map = gardens_site_data_load_file()) && isset($map['sites'])) {
  foreach ($map['sites'] as $domain => $site_details) {
    // Match domain names with the site in SERVER.
    if ($_SERVER['SERVER_NAME'] == $domain) {
      header('Content-Type: application/json');
      $url = 'https://' . $_SERVER['SERVER_NAME'] . '/en/well-known/assetlinks.json';
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($curl, CURLOPT_URL, $url);
      print curl_exec($curl);
      break;
    }
  }
}
