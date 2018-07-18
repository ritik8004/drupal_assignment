<?php

/**
 * @file
 * Hooks specific to the acq_cybersource module.
 */

use Drupal\acq_cart\CartInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allow all modules to validate and update cart data before doing getToken.
 *
 * @param \Drupal\acq_cart\CartInterface $cart
 *   Cart object.
 * @param array $form_data
 *   Form data available in form.
 * @param array $errors
 *   Array to allow modules to respond back with any form errors.
 */
function hook_acq_cybersource_before_get_token_cart_alter(CartInterface $cart, array $form_data, array &$errors) {

}

/**
 * @} End of "addtogroup hooks".
 */
