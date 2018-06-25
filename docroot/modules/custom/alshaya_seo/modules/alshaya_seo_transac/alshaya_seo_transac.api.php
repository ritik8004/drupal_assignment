<?php

/**
 * @file
 * Hooks specific to the alshaya_seo_transac module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter GTM product attributes.
 *
 * @param array $product
 *   The current product.
 * @param array $attributes
 *   SKU attributes.
 */
function hook_gtm_product_attributes_alter(array &$product, array &$attributes) {
}

/**
 * Alter GTM product attributes for pdp page.
 *
 * @param array $product
 *   The current product.
 * @param array $attributes
 *   Sku attributes.
 */
function hook_gtm_pdp_attributes_alter(array &$product, array &$attributes) {
}

/**
 * Alter GTM list name attribute.
 *
 * @param string $listName
 *   Page attibute for GTM.
 */
function hook_gtm_list_name_alter(&$listName) {
}

/**
 * @} End of "addtogroup hooks".
 */
