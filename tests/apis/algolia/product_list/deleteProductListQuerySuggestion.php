<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Create query suggestions for each language for an index.
 */

/**
 * How to use this:
 *
 * Usage: php deleteProductListQuerySuggestion.php [brand] [env] [app_id] [app_secret_admin]
 *
 * Ensure settings.php is updated with proper application id and admin secret
 * key. Once that is done, please go through all the arrays here:
 *
 * $query_facets            Facets used for query suggestion (autocomplete).
 *
 * $query_generate          Additional facets to be used for generating results
 *                          in query suggestions.
 */


require_once __DIR__ . DIRECTORY_SEPARATOR . '../parse_args.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

use Algolia\AlgoliaSearch\SearchClient;

$client = SearchClient::create($app_id, $app_secret_admin);

$name = $prefix . '_' . $product_list_suffix;
$index = $client->initIndex($name);
$settings = $index->getSettings();
$query_suggestion = $name . '_query';
algolia_delete_query_suggestion($app_id, $app_secret_admin, $query_suggestion);
print $query_suggestion . PHP_EOL;
print PHP_EOL . PHP_EOL . PHP_EOL;
sleep(60);
