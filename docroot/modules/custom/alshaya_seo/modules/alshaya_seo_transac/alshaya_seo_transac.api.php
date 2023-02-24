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
 * @param bool $is_indexing
 *   Identifier of the product is indexing to algloia.
 */
function hook_gtm_product_attributes_alter(array &$product, array &$attributes, bool $is_indexing) {
}

/**
 * @} End of "addtogroup hooks".
 */
