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
 * Alter order details before showing in order details section.
 *
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   User object.
 * @param array $product
 *   Product data received from API.
 */
function hook_alshaya_acm_customer_order_details_alter(\Drupal\acq_commerce\SKUInterface $sku, array &$product) {

}

/**
 * @} End of "addtogroup hooks".
 */
