<?php

/**
 * @file
 * Hooks specific to the alshaya_search_api module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the ajax response for language switcher on search page.
 *
 * @param array $query_params
 *   Array of query parameters.
 * @param string $langcode
 *   Langcode in which to translate.
 */
function hook_alshaya_search_api_language_switcher_alter(array &$query_params, $langcode) {

}

/**
 * Adds new page type in the block visibility condition plugin.
 *
 * @param array $page_types
 *   Page Types.
 */
function hook_alshaya_search_api_listing_page_types_alter(array &$page_types) {

}

/**
 * Adds new block id to create facet.
 *
 * @param array $block_data
 *   Block Data.
 */
function hook_alshaya_search_api_facet_block_data_alter(array &$block_data) {

}

/**
 * @} End of "addtogroup hooks".
 */
