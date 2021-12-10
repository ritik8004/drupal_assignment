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
 * Alter order build after processing order details.
 *
 * @param array $order
 *   Order received from API.
 * @param array $build
 *   Build array.
 */
function hook_alshaya_acm_customer_orders_details_build_alter(array &$order, array &$build) {

}

/**
 * Alter order build before showing in recent orders page.
 *
 * @param array $build
 *   Build array.
 */
function hook_alshaya_acm_customer_recent_order_build_alter(array &$build) {

}

/**
 * Alter order summary for order list.
 *
 * @param array $orderRow
 *   Array of order summary.
 */
function hook_alshaya_acm_customer_update_order_summary_alter(array &$order, array &$orderRow) {

}

/**
 * @} End of "addtogroup hooks".
 */
