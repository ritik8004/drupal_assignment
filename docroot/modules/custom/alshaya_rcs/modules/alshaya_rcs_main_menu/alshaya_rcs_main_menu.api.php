<?php

/**
 * @file
 * Hooks specific to the alshaya profile.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the fields to query for the category API.
 *
 * @param array $fields
 *   Fields of the category query.
 */
function hook_alshaya_rcs_category_query_fields_alter(array &$fields, $depth = 0) {
  $fields[] = 'description';
}

/**
 * Allow other modules to modify the variables for Main menu.
 *
 * @param array $variables
 *   Variables used for preparing the block.
 */
function hook_alshaya_rcs_main_menu_alter(array &$variables) {
  // Assign super category from url as category in menu.
  $term = \Drupal::service('alshaya_acm_product_category.product_category_tree')->getCategoryTermFromRoute();
  if ($term) {
    $variables['category_id'] = $term->get('field_commerce_id')->getString();
  }
}

/**
 * @} End of "addtogroup hooks".
 */
