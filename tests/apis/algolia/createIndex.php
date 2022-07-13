<?php
// phpcs:ignoreFile

/**
 * @file
 * Create index and it's replicas for each language.
 */

/**
 * How to use this:
 *
 * Usage: php createIndex.php [brand] [env] [app_id] [app_secret_admin]
 * Example: php createIndex.php mckw 01dev XXXX YYYYYYYYYYYY
 *
 * Ensure settings.php is updated with proper application id and admin secret
 * key. Once that is done, please go through all the arrays here:
 *
 * $languages:              Specify all the languages for which primary indexes
 *                          need to be created.
 *
 * $sorts:                  Replicas need to be created for each sorting option
 *                          required by Views
 *
 * $facets                  Facet fields.
 *
 * $query_facets            Facets used for query suggestion (autocomplete).
 *
 * $query_generate          Additional facets to be used for generating results
 *                          in query suggestions.
 */


require_once __DIR__ . DIRECTORY_SEPARATOR . 'parse_args.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

global $languages;

foreach ($languages as $language) {
  algolia_create_index($app_id, $app_secret_admin, $language, $prefix);
}
