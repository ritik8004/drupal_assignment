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
 * @} End of "addtogroup hooks".
 */
