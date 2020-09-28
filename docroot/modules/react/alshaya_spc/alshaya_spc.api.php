<?php

/**
 * @file
 * Hooks specific to the alshaya_mobile_app module.
 */

use Drupal\acq_commerce\SKUInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter product response data to match brand needs.
 *
 * @param array $data
 *   Product data array to alter.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU entity.
 */
function hook_alshaya_spc_order_sku_details_alter(array &$data, SKUInterface $sku) {

}

/**
 * Allow other modules to change the build array for cart page.
 *
 * @param array $build
 *   Reference to the build array of spc cart.
 */
function hook_alshaya_spc_cart_build_alter(array &$build) {
  $build['#attached']['drupalSettings']['item_code_label'] = 'Item Code';
}

/**
 * @} End of "addtogroup hooks".
 */
