<?php

/**
 * @file
 * Hooks specific to the alshaya_acm_customer module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter user account before saving user account.
 *
 * @param \Drupal\user\Entity\User $user
 *   User object.
 * @param array $customer
 *   Customer data received from API.
 */
function hook_alshaya_acm_customer_update_account_alter(\Drupal\user\Entity\User $user, array &$customer) {

}

/**
 * Allow other modules to alter order detail build array.
 *
 * @param array $build
 *   Build array.
 * @param array $order
 *   Order array.
 */
function hook_alshaya_acm_customer_build_order_detail_alter(array &$build, array $order) {

}

/**
 * @} End of "addtogroup hooks".
 */
