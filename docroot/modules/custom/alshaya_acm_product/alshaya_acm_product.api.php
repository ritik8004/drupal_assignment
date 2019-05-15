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
 * Alter product image for cart.
 *
 * @param array $image
 *   Array of image to alter.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU entity.
 * @param string $context
 *   Context - pdp/search/modal/teaser.
 */
function hook_acq_sku_cart_media_alter(array &$image, SKUInterface $sku, $context = 'cart') {

}

/**
 * @} End of "addtogroup hooks".
 */
