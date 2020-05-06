<?php
// @codingStandardsIgnoreFile

require_once __DIR__ . '/common.php';

$url .= 'accounts/:account_identifier/storage/kv/namespaces/:namespace_identifier/keys';

$url = str_replace(':account_identifier', $account_identifier, $url);
$url = str_replace(':namespace_identifier', $namespace_identifier, $url);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

$headers = array();
$headers[] = 'X-Auth-Email: ' . $email;
$headers[] = 'X-Auth-Key: ' . $key;
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    print 'Error:' . curl_error($ch);
    print PHP_EOL;
}
curl_close($ch);

print_r($result);
print PHP_EOL;
