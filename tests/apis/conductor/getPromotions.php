<?php

/**
 * @file
 * Promotions API.
 */

$promotions_type = isset($argv, $argv[1]) ? $argv[1] : 'cart';

require_once __DIR__ . '/../test.php';

$api = 'agent/promotions/' . $promotions_type;

$method = 'GET';

invoke_api($api);
