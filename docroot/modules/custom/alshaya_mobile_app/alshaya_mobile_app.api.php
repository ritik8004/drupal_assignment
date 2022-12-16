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
 * Alter the output of (payment-methods) resource.
 *
 * @param array $response_data
 *   The output of the API.
 * @param array $exclude_payment_methods
 *   Array of excluded payments.
 */
function hook_alshaya_mobile_app_payment_method_api_response_alter(array &$response_data, array $exclude_payment_methods) {

}

/**
 * Alter product data to match brand needs.
 *
 * @param array $data
 *   Product data array to alter.
 * @param \Drupal\acq_commerce\SKUInterface $sku
 *   SKU entity.
 * @param bool $with_parent_details
 *   Flag to identify whether to get parent details or not.
 */
function hook_alshaya_mobile_app_product_exclude_linked_data_alter(array &$data, SKUInterface $sku, bool $with_parent_details) {

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
 * Alter the output of "categories" (category/all) resource.
 *
 * @param array $data
 *   The output of the API.
 * @param \Drupal\taxonomy\TermInterface $term
 *   The taxonomy term.
 */
function hook_categories_all_response_alter(array &$data, TermInterface $term) {

}

/**
 * @} End of "addtogroup hooks".
 */
