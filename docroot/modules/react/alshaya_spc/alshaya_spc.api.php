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
 * Alter hook to allow other modules to change the order detail settings.
 *
 * @param array $settings
 *   Order details settings.
 * @param array $order
 *   Order data.
 */
function hook_alshaya_spc_order_details_settings_alter(array &$settings, array $order) {

}

/**
 * Allow other modules to change the build array for checkout page.
 *
 * @param array $build
 *   Reference to the build array of checkout cart.
 */
function hook_alshaya_spc_checkout_build_alter(array &$build) {
  $build['#attached']['drupalSettings']['item_code_label'] = 'Item Code';
}

/**
 * Allow other modules to change the build array for checkout login page.
 *
 * @param array $build
 *   Reference to the build array of checkout login page.
 */
function hook_alshaya_spc_checkout_login_build_alter(array &$build) {
  $build['#attached']['drupalSettings']['item_code_label'] = 'Item Code';
}

/**
 * Alter the build for the checkout confirmation page.
 *
 * @param array $build
 *   Checkout confirmation page build data.
 * @param array $order
 *   Order data.
 */
function hook_alshaya_spc_checkout_confirmation_order_build_alter(array &$build, array $order) {
  $build['#attached']['drupalSettings']['item_code_label'] = 'Item Code';
}

/**
 * @} End of "addtogroup hooks".
 */
