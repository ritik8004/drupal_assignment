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
 * @} End of "addtogroup hooks".
 */
