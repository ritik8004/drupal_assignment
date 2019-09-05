<?php

/**
 * @file
 * Hooks specific to the alshaya_acm_product module.
 */

use Drupal\acq_commerce\SKUInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormStateInterface;

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
 * Alter response in ajax callback for add to cart form configurable options.
 *
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   Form state object.
 * @param \Drupal\Core\Ajax\AjaxResponse $response
 *   Current AJAX response.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU Entity for which cart form is submitted.
 * @param \Drupal\acq_commerce\SKUInterface|null $selected_sku
 *   Child SKU based on selected values.
 */
function hook_alshaya_acm_product_add_to_cart_ajax_response_alter(FormStateInterface $form_state, AjaxResponse $response, SKUInterface $sku, SKUInterface $selected_sku = NULL) {

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
