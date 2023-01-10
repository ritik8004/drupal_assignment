<?php
// phpcs:ignoreFile

require_once 'common.php';

$reset_url = $argv[1] ?? '';
$new_password = $argv[2] ?? '';

if (empty($reset_url) || empty($new_password)) {
  print PHP_EOL;
  print 'Please use following format to invoke the script.';
  print PHP_EOL;
  print 'php tests/apis/drupal/reset_password.php "https://aykw8-uat.factory.alshaya.com/en/user/reset/126/1667996741/PX2XNFucRIK9ueSdF-hqBim0piuxdq-yabyPT3N_q64/new/login" "test@1234"';
  print PHP_EOL;
  print 'First argument is the reset link from email and second argument is the new password.';
  print PHP_EOL;
  die();
}

$url_information = parse_url($reset_url);
$domain = $url_information['host'];
$path_information = explode('/', trim($url_information['path'], '/'));
$user_id = $path_information[3];
$timestamp = $path_information[4];
$reset_token = $path_information[5];

$data = [
  'reset_token' => $reset_token,
  'timestamp' => $timestamp,
  'user_id' => $user_id,
  'new_password' => $new_password,
];
if (empty($data['new_password'])) {
  throw new Exception('new_password value not set');
}

$api_url = "https://$domain/rest/v1/user/reset-password";

// Reset password now.
$headers = [];
$headers[] = 'Authorization:Bearer ' . get_token($domain);
$headers[] = 'X-CSRF-Token: ' . get_session_token($domain);
$headers[] = 'Content-Type:application/json';

$data = post($api_url, json_encode($data), $headers);
print_r($data);
print PHP_EOL;
