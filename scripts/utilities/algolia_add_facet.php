<?php

/**
 * @file
 * Add a facet filter in Algolia for given index if Algolia V2 enabled.
 *
 * The first argument is the field name like "sku" or "attr_brand"
 * or "filterOnly(sku). If empty string is passed, we don't proceed further.
 *
 * The second argument is the algolia index name for which we want to add facet.
 * If left empty we use 'alshaya_algolia_product_list_index' default.
 *
 * E.g.
 *
 * @code
 * To add `attr_brand` field as facet filter:
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/algolia_add_facet.php -- "attr_brand" "alshaya_algolia_index"
 *
 * To add `sku` field as a filterOnly facet filter:
 * drush -l local.alshaya-hmae.com scr ../scripts/utilities/algolia_add_facet.php -- "filterOnly(sku)"
 * @endcode
 */

use Drupal\alshaya_search_api\AlshayaSearchApiHelper;

$logger = \Drupal::logger('algolia_add_facet');
// If field for creating facet isn't provided with script return with an error.
if (!$extra[0]) {
  $logger->error('Please provide a field to add as a facet attribute.');
  return;
}

// Get the provided field for creating facet.
$facet_field_to_add = $extra[0];

// Check if algolia V2 is enabled or not. As we are adding facet for the algolia
// V2 index only so we return from here if it's not enabled.
if (!AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index')) {
  $logger->error('Algolia V2 is not enabled for the current site. Please enable it first!.');
  return;
}

// Define default index name to add the facet attribute for.
$index_name = 'alshaya_algolia_product_list_index';
// If index name provided in command use that algolia index for creating facet.
if ($extra[1]) {
  $index_name = $extra[1];
}

// If Algolia V2 enabled, proceed adding facet for the given field.
$logger->notice('Proceeding to add a facet field: @field for algolia index: @index_name.', [
  '@field' => $facet_field_to_add,
  '@index_name' => $index_name,
]);

/** @var \Drupal\alshaya_search_algolia\Service\AlshayaAlgoliaIndexHelper $helper */
$helper = \Drupal::service('alshaya_search_algolia.index_helper');
// Add new filter only sku facet required for my wishlist page to build.
$helper->addCustomFacetToIndex([$facet_field_to_add], $index_name);

// If index name is available proceed adding facet for the given field.
$logger->notice('A new facet field: @field for algolia index: @index_name has been added successfully.', [
  '@field' => $facet_field_to_add,
  '@index_name' => $index_name,
]);
