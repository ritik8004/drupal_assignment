<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Stock check.
 */

// Direct magento Call:
// curl -v -H "Accept: application/json" -H "Authorization:bearer 5xedvmt50opiu9mw4hd4h92im00kntnf" -X GET https://master-7rqtwti-z3gmkbwmwrl4g.eu.magentosite.cloud/rest/V1/stockItems/VK%20Test%20Checkout%20004

$sku = isset($argv, $argv[1]) ? $argv[1] : '11704';

require_once __DIR__ . '/../test.php';

$api = 'agent/stock/' . $sku;

invoke_api($api);
