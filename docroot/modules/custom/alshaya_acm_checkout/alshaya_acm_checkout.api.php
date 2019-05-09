<?php

/**
 * @file
 * Hooks specific to the alshaya_acm_checkout module.
 */

use Drupal\acq_cart\CartInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Do do any changes/operations required on cart before order is finally placed.
 *
 * @param \Drupal\acq_cart\CartInterface $cart
 *   Cart to process.
 */
function hook_alshaya_acm_checkout_pre_place_order(CartInterface $cart) {

}

/**
 * Alter response for home delivery save address.
 *
 * @param object $response
 *   The ajax response object.
 * @param string $plugin_id
 *   The plugin id.
 */
function hook_home_delivery_save_address_alter($response, $plugin_id) {

}

/**
 * @} End of "addtogroup hooks".
 */
