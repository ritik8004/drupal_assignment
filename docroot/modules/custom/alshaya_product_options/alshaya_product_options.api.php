<?php

/**
 * @file
 * Hooks specific to the alshaya_product_options module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allow other modules to alter search filter link.
 *
 * @param string $link
 *   Brand link to alter.
 * @param array $data
 *   Data array.
 */
function hook_alshaya_search_filter_link_alter(string &$link, array &$data) {
}

/**
 * Allow other modules to alter product options.
 *
 * @param array $attribute
 *   Array of product options attribute.
 */
function hook_product_attribute_options_alter(array &$attribute) {
}

/**
 * @} End of "addtogroup hooks".
 */
