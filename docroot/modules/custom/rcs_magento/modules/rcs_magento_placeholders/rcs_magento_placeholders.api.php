<?php

/**
 * @file
 * Hooks specific to the rcs_magento_placeholders module.
 */

/**
 * Implements hook_rcs_graphql_query_fields().
 */
function hook_rcs_graphql_query_fields() {
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
 * Implements hook_rcs_graphql_query_fields_alter().
 */
function hook_rcs_graphql_query_fields_alter(&$query_fields) {
  // Alter existing query fields.
  $query_fields['categories']['items'][] = 'id';
  unset($query_fields['categories']['items']['image']);
}
