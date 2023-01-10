<?php

/**
 * @file
 * Hooks specific to the alshaya_acm_product_category module.
 */

use Drupal\taxonomy\TermInterface;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the output of "acm_product_category_tree_data" resource.
 *
 * @param array $data
 *   The data output.
 * @param \Drupal\taxonomy\TermInterface $term
 *   The taxonomy term.
 * @param string $langcode
 *   The language code.
 */
function hook_alshaya_acm_product_category_tree_data_alter(array &$data, TermInterface $term, $langcode) {

}

/**
 * @} End of "addtogroup hooks".
 */
