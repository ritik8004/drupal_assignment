<?php

/**
 * @file
 * Registration API.
 */

$customer = [];
$customer['firstname'] = 'curl';
$customer['lastname'] = 'curl';
$customer['email'] = 'me+curl@nik4u.com';

$pass = 'test@123';

require_once __DIR__ . '/../test.php';

$api = 'agent/customer';

$method = 'POST';

$data = [];
$data['json']['customer'] = $customer;
$data['json']['password'] = $pass;

invoke_api($api, $method, $data);
