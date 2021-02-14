<?php

/**
 * @file
 * Hooks specific to the alshaya_acm_product module.
 */

use Drupal\acq_commerce\SKUInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter product build to match brand needs.
 *
 * @param array $build
 *   Build array to alter.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU entity.
 * @param string $context
 *   Context - pdp/search/modal/teaser.
 * @param string $color
 *   Color value used to show the product.
 */
function hook_alshaya_acm_product_build_alter(array &$build, SKUInterface $sku, $context = 'pdp', $color = '') {

}

/**
 * Alter product gallery to match brand needs.
 *
 * @param array $gallery
 *   Gallery array to alter.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU entity.
 * @param string $context
 *   Context - pdp/search/modal/teaser.
 */
function hook_alshaya_acm_product_gallery_alter(array &$gallery, SKUInterface $sku, $context = 'pdp') {

}

/**
 * Alter light product data.
 *
 * @param \Drupal\acq_sku\Entity\SKU $sku
 *   SKU object.
 * @param array $data
 *   Light product data that needs to be altered.
 * @param mixed $type
 *   Type of product - `light` or `full`.
 *
 * @see \Drupal\alshaya_acm_product\Service\SkuInfoHelper::getLightProduct()
 * @see \Drupal\alshaya_acm_product\Plugin\rest\resource\ProductResource::getSkuData()
 */
function hook_alshaya_acm_product_light_product_data_alter(\Drupal\acq_sku\Entity\SKU $sku, array &$data, $type) {
  $test_data = [];
  $data['test'] = $test_data;
}

/**
 * Allow other modules to alter media items array for products.
 *
 * @param array $media
 *   Media data to alter.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU entity.
 */
function hook_alshaya_acm_product_media_items_alter(array $media, SKUInterface $sku) {

}

/**
 * Allow other modules to add/alter variant info.
 *
 * @param array $variant
 *   Variant data.
 * @param \Drupal\acq_commerce\SKUInterface $child
 *   Variant SKU Entity.
 * @param \Drupal\acq_commerce\SKUInterface|null $parent
 *   Parent SKU Entity if available.
 */
function hook_sku_variant_info_alter(array &$variant, SKUInterface $child, ?SKUInterface $parent) {

}

/**
 * Allow other modules to add/alter product info.
 *
 * @param array $product_info
 *   Product info data.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU Entity.
 */
function hook_sku_product_info_alter(array &$product_info, SKUInterface $sku) {

}

/**
 * Allow other modules to add/alter context key.
 *
 * @param string $context
 *   Context for which layout needs to be fetched.
 * @param string $pdp_layout
 *   Context for which layout needs to be fetched.
 */
function hook_alshaya_context_key_from_layout_alter(string $context, string $pdp_layout) {

}

/**
 * Allow other modules to alter the data in recommended skus list.
 *
 * @param string $type
 *   The type of the recommended product, eg. crosssel, upsell or related.
 * @param array $recommended_skus
 *   Array of related skus data keyed by sku.
 */
function hook_alshaya_acm_product_recommended_products_data_alter(string $type, array &$recommended_skus) {

}

/**
 * Allow other modules to change the build array for PDP size-guide modal.
 *
 * @param array $build
 *   Reference to the build array.
 */
function hook_alshaya_acm_product_modal_build_alter(array &$build) {

}

/**
 * Allow other modules to alter removeDisabledProducts.
 *
 * @param array $data
 *   Disabled Product Data.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU Entity.
 */
function hook_alshaya_acm_product_remove_disabled_products_alter(array &$data, SKUInterface $sku) {
  $data['skipSkuDelete'] = TRUE;
}

/**
 * @} End of "addtogroup hooks".
 */
