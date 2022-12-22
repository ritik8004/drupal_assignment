<?php

/**
 * @file
 * Document all supported APIs.
 */

/**
 * Describe the block listing products which can be used by contributors.
 */
function hook_rcs_placeholders_product_list_block_info() {
  return [
    'product_list' => [
      'label' => t('Product list'),
      'id' => 'product_list',
      'param' => [
        'backend' => 'commerce',
      ],
    ],
  ];
}

/**
 * Alter the product list block info returned by other modules.
 *
 * @param array $info
 *   Info to alter.
 */
function hook_rcs_placeholders_product_list_block_info_alter(array &$info) {
  $info['product_list']['param']['backend'] = 'search';
}

/**
 * Allow the master modules to provide GraphQL queries.
 */
function hook_rcs_placeholders_graphql_query() {
  // You should return an array containing the name of the query followed by the
  // respective fields.
  return [
    'categories' => [
      'total_count',
      'items' => [
        'name',
        'url_path',
        'description',
        'image',
      ],
    ],
  ];
}

/**
 * Allow the site specific modules to override the GraphQL queries.
 *
 * @param array $queries
 *   Queries to alter.
 */
function hook_rcs_placeholders_graphql_query_alter(array &$queries) {
  // Alter existing query fields.
  $queries['categories']['items'][] = 'id';
  unset($queries['categories']['items']['image']);
}
