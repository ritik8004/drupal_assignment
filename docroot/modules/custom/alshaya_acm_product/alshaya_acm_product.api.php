<?php

/**
 * @file
 * Hooks specific to the alshaya_acm_product module.
 */

use Drupal\acq_commerce\SKUInterface;
use Drupal\Core\Entity\EntityInterface;

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
 * Alter Gift product data.
 *
 * @param \Drupal\acq_sku\Entity\SKU $sku
 *   SKU object.
 * @param array $data
 *   Gift product data that needs to be altered.
 * @param mixed $type
 *   Type of product - `light` or `full`.
 *
 * @see \Drupal\alshaya_acm_product\Service\SkuInfoHelper::getLightProduct()
 * @see \Drupal\alshaya_acm_product\Plugin\rest\resource\ProductResource::getSkuData()
 */
function hook_alshaya_acm_product_gift_product_data_alter(\Drupal\acq_sku\Entity\SKU $sku, array &$data, $type) {
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
 * @param array $data
 *   Contains all the necessary data to pass to the hook implementations.
 */
function hook_alshaya_acm_product_recommended_products_data_alter(array $data) {
  if ($data['type'] !== 'crosssell' || $data['format'] === 'json') {
    return;
  }
  $data['data']['section_title'] = t('Frequently Bought Together', [], ['context' => 'alshaya_static_text|pdp_matchback_title']);
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
 * Allow other modules to add/alter attributes value.
 *
 * @param array $attributes
 *   Product attributes data.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU Entity.
 * @param string $field_name
 *   Product attribute field name.
 */
function hook_sku_product_attribute_alter(array &$attributes, SKUInterface $sku, string $field_name) {

}

/**
 * Allow other modules to add/alter the swatch type.
 *
 * On PDP page, we render color attribute. It is possible to render
 * the color attribute attribute as combination of multiple attributes.
 * Thus also the render type like swatch text etc. This allows other
 * moduls to alter the swatch type.
 *
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU Entity.
 * @param array $color_options_list
 *   Color attribute option list.
 * @param \Drupal\acq_commerce\SKUInterface $variant_sku
 *   Variant SKU Entity.
 */
function hook_alshaya_acm_product_pdp_swath_type_alter(SKUInterface $sku, array &$color_options_list, SKUInterface $variant_sku) {

}

/**
 * Allow other modules to alter the SKU form data.
 *
 * On PDP page, we render the add to cart form i.e. SKU base form.
 * This hook will allow other modules to alter the SKU base form data
 * after the sku base form alter executes in `alshaya_acm_product.module`.
 *
 * @param array $form
 *   Nested array of form elements that comprise the form.
 * @param \Drupal\acq_commerce\SKUInterface $sku_entity
 *   SKU Entity.
 */
function hook_alshaya_acm_product_skubaseform_alter(array &$form, SKUInterface $sku_entity) {

}

/**
 * Allow other modules to alter the context array during product process.
 *
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   Node entity.
 * @param array $context
 *   An array containing a list of all the contexts used during product process.
 */
function hook_alshaya_acm_product_process_product_context_alter(EntityInterface $entity, array &$context) {

}

/**
 * @} End of "addtogroup hooks".
 */
