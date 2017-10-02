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

$method = 'JSON';

$data = [];
$data['customer'] = $customer;
$data['password'] = $pass;

invoke_api($api, $method, $data);
