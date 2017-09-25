<?php

/**
 * @file
 * Get customer.
 *
 * Usage: php -f tests/apis/conductor/getcustomer.php email.
 */

$email = isset($argv, $argv[1]) ? $argv[1] : 'me@nik4u.com';

require_once __DIR__ . '/../test.php';

$api = 'agent/customer/' . $email;

invoke_api($api);
