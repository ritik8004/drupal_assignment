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
 * @} End of "addtogroup hooks".
 */
