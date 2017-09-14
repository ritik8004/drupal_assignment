<?php

/**
 * @file
 * Get cart.
 *
 * Usage: php -f tests/apis/conductor/getcart.php 123.
 */

$cart_id = isset($argv, $argv[1]) ? $argv[1] : 6112;

$sku = isset($argv, $argv[2]) ? $argv[2] : 'M-HB141  61 140';

require_once __DIR__ . '/../test.php';

$api = 'agent/cart/' . $cart_id;

$options = [];

$data = [
  'items' => [
    ['sku' => $sku, 'qty' => 1],
  ],
];

invoke_api($api, 'JSON', $data, 1);
