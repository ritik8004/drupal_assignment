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
 * Alter product data to match brand needs.
 *
 * @param array $data
 *   Product data array to alter.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU entity.
 */
function hook_alshaya_mobile_app_product_exclude_linked_data_alter(array &$data, SKUInterface $sku) {

}

/**
 * Alter the output of "options_list" resource.
 *
 * @param array $data
 *   The output of the API.
 */
function hook_options_list_resource_response_alter(array &$data) {

}

/**
 * @} End of "addtogroup hooks".
 */
