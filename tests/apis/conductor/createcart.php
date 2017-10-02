<?php

/**
 * @file
 * Get cart.
 *
 * Usage: php -f tests/apis/conductor/getcart.php 123.
 */

require_once __DIR__ . '/../test.php';

$api = 'agent/cart/create';

invoke_api($api, 'POST');
