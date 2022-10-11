<?php

/**
 * @file
 * Hooks specific to the Alshaya RCS bazaar voice .
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the fields for Bazaar voice product.
 *
 * @param array &$fields
 *   Fields for BV product.
 */
function hook_alshaya_rcs_product_bv_product_fields_alter(array &$fields) {
  array_push($fields['items'], 'name');
}

/**
 * @} End of "addtogroup hooks".
 */
