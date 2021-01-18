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
 * Alter order data before showing in order details.
 *
 * @param array $order
 *   Order received from API.
 */
function hook_alshaya_acm_customer_order_details_alter(array &$order) {

}

/**
 * Alter build array of alshaya_user_recent_orders block.
 *
 * @param array $build
 *   Block build array.
 */
function hook_alshaya_acm_customer_alshaya_user_recent_orders_build_alter(array &$build) {

}

/**
 * @} End of "addtogroup hooks".
 */
