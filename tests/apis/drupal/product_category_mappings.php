<?php
// phpcs:ignoreFile

/*
CURL request to get token

curl -X POST \
'https://hmkw-test.factory.alshaya.com/oauth/token?_format=json' \
  -H 'Cache-Control: no-cache' \
  -d 'username=alshaya_magento&password=AlShAyA_MaGeNtO&client_id=4cacd535-3b24-434e-9d32-d6e843f7b91a&client_secret=AlShAyA&grant_type=password'

*/

/*
CURL request to get images

curl -X POST \
  'https://hmkw-test.factory.alshaya.com/en/rest/v1/product-category-mapping?_format=json' \
  -H 'Cache-Control: no-cache' \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImIwZjMyYmRjNGNkMDVmMDllYmE0MDc5OTI5OTdiZTgyZmM5ZTg5ZDZkM2I2YzU4NzQ2ZjFlYjZjMWM1MjI3MjZiOGYwODUwZTAzYjY3MjI5In0.eyJhdWQiOiI0Y2FjZDUzNS0zYjI0LTQzNGUtOWQzMi1kNmU4NDNmN2I5MWEiLCJqdGkiOiJiMGYzMmJkYzRjZDA1ZjA5ZWJhNDA3OTkyOTk3YmU4MmZjOWU4OWQ2ZDNiNmM1ODc0NmYxZWI2YzFjNTIyNzI2YjhmMDg1MGUwM2I2NzIyOSIsImlhdCI6MTUzNjkyNDM4MywibmJmIjoxNTM2OTI0MzgzLCJleHAiOjE1MzY5MjQ2ODMsInN1YiI6IjEyNTI2Iiwic2NvcGVzIjpbImF1dGhlbnRpY2F0ZWQiLCJhbHNoYXlhX21hZ2VudG9fY29uc3VtZXIiXX0.pYNNbOvz2JkHOaVtH_X-kdEbwOC_nldoKM9RbDSRf_09kwkhCX_SODFaTN-Na5aygRWF2DU762cybWwOF_YCOtneqhpBrwyUht0sWejUlsU6XEjQ4OOLNsfxti-xBniEFU6vTiItAxLuXLAxo91virljJoGtKDevMKoeHgYl1V0qeuQ6gBWN-a0V16VTbLiBujo7TkJPWVhwVih_yocnX_s0XrLfWGxEpWN9RyHCwWVU4CimaHaNHsJEIVtVC44Kj8Z3f-os12zN40Rq0JiUgzAPUscU4bHjp2DHMCcOHPdUvrJT2nUh_TACChVbvWa5x-xLtaCpERSQ-L0QehagdMeImWDddalHQ59qDygxZcQq6aBimnoO0yUgg_CjwSNLgQGKTj5npFw5EdcHlvHdrAh46f9b5X7xYf0z8SyRRHCdPdWG3C4dSs0evHqjipt1sx8GtvO6_RRT_0PlSTK5Drs3TYtWCzQEeEgyIfOM7nuj5YxrM5q7IOgkaQigwULpT9wwR6dkCpMMozGgI2jYSPAcDFilfMyts47zhyVAk30BFCvzYRq7C8FXI4VEXsSen9yZIoqlxci1pHO7VSrSwirSQ6epkkNEMX7l5ExbEX5H0eFKOph8tohHYpVv4U1dHK3fynszFdvIJdkHaXQq9itug9ROseuxBanG_EoM3ZY' \
  -d '{"skus": ["M-D2937     777777", "0351484", "0526708"], "langcode": "kwt_en"}'

*/

/* Edit below to set proper env, skus, langcode */

$base_url = 'https://local.alshaya-vsae.com/';

$data = [
  [
    'sku' => 'SKU',
    'categories' => [1, 2, 3]
  ],
  [
    'sku' => 'SKU2',
    'categories' => [3, 4]
  ],
];

/* Edit above to set proper env, skus, langcode */

$client_id = '4cacd535-3b24-434e-9d32-d6e843f7b91a';
$client_secret = 'AlShAyA';
$grant_type = 'client_credentials';

$token_api_url = $base_url . 'oauth/token?_format=json';
$api_url = $base_url . 'rest/v1/product-category-mapping?_format=json&XDEBUG_SESSION_START=PHPSTORM';

function post($url, $data, array $headers = [])
{
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

// Get token first.
$token_data = [
    'client_id'     => $client_id,
    'client_secret' => $client_secret,
    'grant_type'    => $grant_type,
];

$token_info = post($token_api_url, http_build_query($token_data));
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

    $token = $token_info['access_token'];
}

// Get skus data now.
$headers = [];
$headers[] = 'Authorization:Bearer ' . $token;
$headers[] = 'Content-Type:application/json';

$data = post($api_url, json_encode($data), $headers);
print_r($data);
print PHP_EOL;
