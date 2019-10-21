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
 *
 * @see \Drupal\alshaya_acm_product\Service\SkuInfoHelper::getLightProduct()
 */
function hook_alshaya_acm_product_light_product_data_alter(\Drupal\acq_sku\Entity\SKU $sku, array &$data) {
  $test_data = [];
  $data['test'] = $test_data;
}

/**
 * Alter full product data.
 *
 * @param \Drupal\acq_sku\Entity\SKU $sku
 *   SKU object.
 * @param array $data
 *   Full product data that needs to be altered.
 *
 * @see \Drupal\alshaya_acm_product\Plugin\rest\resource\ProductResource::getSkuData()
 */
function hook_alshaya_acm_product_full_product_data_alter(\Drupal\acq_sku\Entity\SKU $sku, array &$data) {
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
 * @param \Drupal\acq_commerce\SKUInterface $parent
 *   Parent SKU Entity.
 */
function hook_sku_variant_info_alter(array &$variant, SKUInterface $child, SKUInterface $parent) {
  \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
  $variant['click_collect'] = alshaya_acm_product_available_click_collect($child);
}

/**
 * @} End of "addtogroup hooks".
 */
