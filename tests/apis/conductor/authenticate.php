<?php

/**
 * @file
 * Authentication API.
 */

$mail = 'me+l5@nik4u.com';
$pass = 'test@123';

require_once __DIR__ . '/../test.php';

$api = 'agent/customer/' . $mail;

$method = 'POST';

$data = [];
$data['password'] = $pass;

invoke_api($api, $method, $data);
