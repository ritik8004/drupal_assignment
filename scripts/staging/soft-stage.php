<?php

global $user;
$user = 'vincent.bouchet';

global $api_key;
$api_key = 'baceaa29994194954c3cd0139c6a332b7603edd1';

function acsf_api($endpoint) {
  global $user;
  global $api_key;

  $url = 'https://www.alshaya.acsitefactory.com/api/v1/' . $endpoint;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_USERPWD, "$user:$api_key");
  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  $output = curl_exec($ch);
  curl_close($ch);

  return json_decode($output);
}

function acsf_get_site_id_from_name($name) {
  $sites_data = acsf_api('sites?limit=100');

  foreach ($sites_data->sites as $site) {
    if ($name == $site->site) {
      return $site->id;
    }
  }

  return FALSE;
}
/*
$sites_str = $argv[1];

foreach (explode(';', $argv[1]) as $index => $batch) {
  $sites_id = [];

  foreach (explode(',', $batch) as $site_name) {
    if (($site_id = acsf_get_site_id_from_name($site_name)) !== FALSE) {
      $sites_id[] = $site_id;
    }
  }
}*/
