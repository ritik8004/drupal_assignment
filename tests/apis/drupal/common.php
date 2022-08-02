<?php
// phpcs:ignoreFile

function post($url, $data, array $headers = []) {
  $headers['Cache-Control'] = 'no-cache';

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  $return = curl_exec($ch);

  $error = curl_error($ch);

  if ($error) {
    print_r($error);
    print PHP_EOL;
    die();
  }

  curl_close($ch);

  return $return;
}

function get_session_token(string $domain) {
  $url = "https://$domain/en/session/token?_format=json";

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

  $headers = [
    "Content-Type: application/json",
  ];
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

  $response = curl_exec($curl);
  curl_close($curl);

  print $response . PHP_EOL;
  return $response;
}

function get_token(string $domain) {
  $url = "https://$domain/oauth/token?_format=json";

  // Get token first.
  $token_data = [
    'client_id'     => 'ac73dcc7-6918-4e14-8b48-86b5cd17f4d2',
    'client_secret' => 'AlShAyA',
    'grant_type'    => 'password',
    'username' => 'alshaya_mobile_app',
    'password' => 'AlShAyA_MoBiLe',
  ];

  $token_info = post($url, http_build_query($token_data));
  if ($token_info) {
    $token_info = json_decode($token_info, true);

    if (!is_array($token_info) || empty($token_info)) {
      print 'Error: Not able to get token' . PHP_EOL;
      die();
    }

    if (empty($token_info['access_token'])) {
      print 'Error: Token bearer empty.' . PHP_EOL;
      die();
    }

    print $token_info['access_token'] . PHP_EOL;
    return $token_info['access_token'];
  }

  return null;
}

