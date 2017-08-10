<?php

/**
 * @file
 * Get cc token.
 */

$cart_id = isset($argv, $argv[1]) ? $argv[1] : 11702;
$card_type = isset($argv, $argv[2]) ? $argv[2] : 'VI';

$opt = [];
$opt['query']['card_type'] = $card_type;
$opt['query']['cart_id'] = $cart_id;

require_once __DIR__ . '/../test.php';

$api = 'agent/cart/token/cybersource';

invoke_api($api, 'GET', $opt);
