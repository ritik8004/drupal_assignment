<?php

/**
 * @file
 * Hooks specific to the alshaya_rcs_product module.
 */

/**
 * Allows modules to alter the alshaya rcs order build array.
 *
 * @param array $build
 *   The build array.
 */
function hook_alshaya_rcs_product_order_build_alter(array &$build) {
}

/**
 * Allows modules to alter the fields of the graphql product query fields.
 *
 * @param array $fields
 *   The graphql product query fields.
 */
function hook_alshaya_rcs_product_query_fields_alter(array &$fields) {
  array_push($fields['items'], 'style_code');
}

/**
 * Allows modules to alter the variables for the product options graphql query.
 */
function hook_alshaya_rcs_product_product_options_to_query() {
  return ['size'];
}

/**
 * Alter the product query fields for recent orders section.
 *
 * @param array $fields
 *   Fields of the product query for recent orders section.
 */
function hook_alshaya_rcs_product_recent_orders_fields_alter(array &$fields) {
  array_push(
    $fields['items']['... on ConfigurableProduct']['variants']['product'],
    'assets_teaser',
  );
}

/**
 * Alter the product query fields for order details section.
 *
 * @param array $fields
 *   Fields of the product query for order details section.
 */
function hook_alshaya_rcs_product_order_details_fields_alter(array &$fields) {
  array_push(
    $fields['items']['... on ConfigurableProduct']['variants']['product'],
    'color',
  );
}
