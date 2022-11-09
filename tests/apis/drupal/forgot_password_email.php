<?php
// phpcs:ignoreFile

require_once 'common.php';

$domain = $argv[1] ?? '';
$email = $argv[2] ?? '';

if (empty($domain) || empty($email)) {
  print PHP_EOL;
  print 'Please use following format to invoke the script.';
  print PHP_EOL;
  print 'php tests/apis/drupal/forgot_password_email.php aykw8-uat.factory.alshaya.com "ilchuk.se@gmail.com"';
  print PHP_EOL;
  die();
}

$api_url = "https://$domain/en/rest/v1/user/forgot-password-email?_format=json";

// Reset password now.
$headers = [];
$headers[] = 'Authorization:Bearer ' . get_token($domain);
$headers[] = 'X-CSRF-Token: ' . get_session_token($domain);
$headers[] = 'Content-Type:application/json';

$data = [];
$data['email'] = $email;

$response = post($api_url, json_encode($data), $headers);
print_r($response);
print PHP_EOL;
