<?php
// phpcs:ignoreFile

require_once 'common.php';

/* Edit below to set as per requirement */

$domain = 'bbwae-uat.factory.alshaya.com';

$data = [
  'reset_token' => 'QcBPAkXuxNEplKQ8x89jCj2eIgCijImbYULQfOgMUIo',
  'timestamp' => 1_641_891_870,
  'user_id' => 2_241_391,
  'new_password' => 'Alshaya@2023',
];

/* Edit above to set proper env, skus, langcode */


$api_url = "https://$domain/rest/v1/user/reset-password";

// Reset password now.
$headers = [];
$headers[] = 'Authorization:Bearer ' . get_token($domain);
$headers[] = 'X-CSRF-Token: ' . get_session_token($domain);
$headers[] = 'Content-Type:application/json';

$data = post($api_url, json_encode($data), $headers);
print_r($data);
print PHP_EOL;
