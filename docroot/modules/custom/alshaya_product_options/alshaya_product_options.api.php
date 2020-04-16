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
 * @param string $attribute_code
 *   Brand Attribute code.
 * @param string $value
 *   The attribute value.
 */
function hook_alshaya_search_filter_link_alter(string &$link, string $attribute_code, string $value) {
}

/**
 * @} End of "addtogroup hooks".
 */
