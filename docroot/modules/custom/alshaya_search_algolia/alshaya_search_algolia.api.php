<?php

/**
 * @file
 * Hooks specific to the alshaya_search_algolia module.
 */

use Drupal\Core\Entity\EntityInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the list of attributes to excluded for ProductList Indexing.
 *
 * @param array $excludedAttributes
 *   Array of query parameters.
 */
function hook_alshaya_product_list_exclude_attribute_alter(array &$excludedAttributes) {

}

/**
 * Alter the list of attributes to excluded for ProductList Indexing.
 *
 * @param array $item_ids
 *   Array of query parameters.
 * @param object $index
 *   Object of index.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   EntityInterface of entity.
 */
function hook_alshaya_search_api_entity_delete_alter(array &$item_ids, $index, EntityInterface $entity) {

}

/**
 * Alter the list of attributes to excluded for ProductList Indexing.
 *
 * @param array $item_ids
 *   Array of query parameters.
 * @param object $index
 *   Object of index.
 * @param string $id
 *   String of query parameters.
 */
function hook_alshaya_search_api_track_items_alter(array &$item_ids, $index, $id) {

}

/**
 * @} End of "addtogroup hooks".
 */
