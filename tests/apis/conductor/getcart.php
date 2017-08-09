<?php

/**
 * @file
 * Get cart.
 */

$cart_id = 11704;

require_once __DIR__ . '/../test.php';

$api = 'agent/cart/' . $cart_id;

invoke_api($api);
