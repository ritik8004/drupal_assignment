<?php

/**
 * @file
 * Hooks specific to the alshaya_search_algolia module.
 */

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
 * Add/Alter ranking & sorting for any indexable attribute.
 *
 * @param array $replica_settings
 *   Array of replica settings.
 * @param array $sort
 *   Array of fields to be sorted.
 * @param array $ranking
 *   Array of fields with ranking.
 */
function hook_alshaya_search_algolia_ranking_sorting_alter(array &$replica_settings, array $sort, array &$ranking) {

}

/**
 * Add/Alter attribute data to be indexed.
 *
 * @param array $object
 *   Array of index object.
 */
function hook_alshaya_search_algolia_object_alter(array &$object) {

}

/**
 * @} End of "addtogroup hooks".
 */
