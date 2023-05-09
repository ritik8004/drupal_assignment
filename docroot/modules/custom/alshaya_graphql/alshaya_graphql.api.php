<?php

/**
 * @file
 * Document all supported APIs.
 */

/**
 * Allow other modules to provide GraphQL queries.
 */
function hook_alshaya_graphql_query() {
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
function hook_alshaya_graphql_query_alter(array &$queries) {
  // Alter existing query fields.
  $queries['categories']['items'][] = 'id';
  unset($queries['categories']['items']['image']);
}
