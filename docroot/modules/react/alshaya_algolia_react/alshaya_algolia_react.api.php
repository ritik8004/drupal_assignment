<?php

/**
 * @file
 * Hooks specific to the alshaya_algolia_react module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter or add extra configs in agolia react common configurations.
 *
 * @param array $response
 *   Response array to alter.
 */
function hook_algolia_react_common_configs_alter(array &$response) {

}

/**
 * Returns page type information for option list.
 *
 * @param string $query_type
 *   Query type of the page.
 * @param string $page_type
 *   Page type.
 * @param string $page_sub_type
 *   Page sub type.
 */
function hook_algolia_react_option_list_information_alter($query_type, &$page_type, &$page_sub_type) {

}

/**
 * @} End of "addtogroup hooks".
 */
