<?php

/**
 * @file
 * Get cart.
 */

$cart_id = isset($argv, $argv[1]) ? $argv[1] : 11704;

$json = [
  'firstname' => 'Nikunj',
  'lastname' => 'Kotecha',
  'telephone' => '+96567701234',
  'street' => 'B',
  'extension' => [
    'address_apartment_segment' => '',
    'address_area_segment' => 'Abraq Khaitan',
    'address_building_segment' => 'Building',
    'address_block_segment' => 'Block',
  ],
  'country_id' => 'KW',
  'city' => '&#8203;',
];

require_once __DIR__ . '/../test.php';

$api = 'agent/cart/' . $cart_id . '/estimate';

invoke_api($api, 'JSON', $json);
