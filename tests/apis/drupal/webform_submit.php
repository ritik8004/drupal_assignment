<?php
// phpcs:ignoreFile

require_once 'common.php';

/* Edit below to set as per requirement */

$domain = 'mckw4-uat.factory.alshaya.com';

$data = [
  'webform_id' => 'alshaya_contact',
  'first_name' => 'Test',
  'last_name' => 'Test',
  'mobile_number' => '+96567701236',
  'select_your_preference_of_channel_of_communication' => 'Mobile',
  'email' => 'test@test.com',
  'message' => 'message test',
  'feedback' => 'online_shopping',
  'type' => 'complaint',
  'reason1' => 'wrong_delivery',
  'request_from' => 'mapp',
];

/* Edit above to set proper env, skus, langcode */


$api_url = "https://$domain/webform_rest/submit?_format=json&XDEBUG_SESSION_START=PHPSTORM";

// Reset password now.
$headers = [];
$headers[] = 'Authorization:Bearer ' . get_token($domain);
$headers[] = 'X-CSRF-Token: ' . get_session_token($domain);
$headers[] = 'Content-Type:application/json';

$data = post($api_url, json_encode($data), $headers);
print_r($data);
print PHP_EOL;
