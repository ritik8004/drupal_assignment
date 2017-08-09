<?php

/**
 * @file
 * Stock check.
 */

$sku = 'M-D2937     777777';

require_once __DIR__ . '/../test.php';

$api = 'agent/stock/' . $sku;

invoke_api($api);
