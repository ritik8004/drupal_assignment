<?php

/**
 * @file
 * Get cart.
 *
 * Usage: php -f tests/apis/conductor/getcart.php 123.
 */

$cart_id = isset($argv, $argv[1]) ? $argv[1] : 11704;

require_once __DIR__ . '/../test.php';

$api = 'agent/cart/' . $cart_id . '/payments';

invoke_api($api);
