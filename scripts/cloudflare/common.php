<?php
// @codingStandardsIgnoreFile

if (file_exists(__DIR__ . '/settings.php')) {
  require_once 'settings.php';
}
else {
  $home = isset($_ENV['AH_SITE_ENVIRONMENT']) ? $_SERVER['HOME'] : '/home/vagrant';
  require_once $home . '/alshaya_cloudflare_settings.php';
}

function get_zone_for_domain(string $domain) {
  // Remove the first level.
  $domain_to_check = explode('.', $domain);
  array_shift($domain_to_check);
  $domain_to_check = implode('.', $domain_to_check);

  $data = [
    'page' => 1,
    'per_page' => 20,
    'name' => $domain_to_check,
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones';
  $api_url .= '?' . http_build_query($data);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $api_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


  $headers = [];
  $headers[] = 'Content-Type: application/json';
  $headers[] = 'Authorization: Bearer ' . $GLOBALS['api_key'];
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
    return '';
  }
  curl_close($ch);

  $result = json_decode($result, TRUE);
  foreach ($result['result'] ?? [] as $row) {
    return $row['id'];
  }

  return '';
}

function clear_cache_for_domain(string $zone, string $domain) {
  $data = [
    'prefixes' => [
      $domain . '/en',
      $domain . '/ar',
    ],
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/purge_cache';

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $api_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

  $headers = [];
  $headers[] = 'Content-Type: application/json';
  $headers[] = 'Authorization: Bearer ' . $GLOBALS['api_key'];
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
    return '';
  }
  curl_close($ch);

  return $result;
}

function clear_cache_for_url(string $zone, string $domain, string $url) {
  $data = [
    'files' => ['https://' . $url],
  ];

  $api_url = 'https://api.cloudflare.com/client/v4/zones/' . $zone . '/purge_cache';

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $api_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

  $headers = [];
  $headers[] = 'Content-Type: application/json';
  $headers[] = 'Authorization: Bearer ' . $GLOBALS['api_key'];
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
    return '';
  }
  curl_close($ch);

  return $result;
}
