<?php

/**
 * @file
 * Get linked Skus.
 *
 * Usage: php -f tests/apis/conductor/getLinkedSkus.php '0581378' 'crosssell'.
 */

$sku = isset($argv, $argv[1]) ? $argv[1] : '0581378';

$type = isset($argv, $argv[2]) ? $argv[2] : 'crosssell';

require_once __DIR__ . '/../test.php';

$api = "agent/product/" . $sku . "/related/" . $type;

invoke_api($api);
