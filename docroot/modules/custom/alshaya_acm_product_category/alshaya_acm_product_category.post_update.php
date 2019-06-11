<?php

/**
 * @file
 * Post update functions for alshaya_acm_product_category.
 */

/**
 * Set default values for newly created fields.
 */
function alshaya_acm_product_category_post_update_8030() {
  // Set include in desktop and mobile to true, to make existing links visible
  // on all devices as is.
  foreach (['field_include_in_desktop', 'field_include_in_mobile_tablet'] as $field_name) {
    $tids = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', 'acq_product_category')
      ->condition($field_name, '', 'IS NULL')
      ->execute();

    if (!empty($tids)) {
      foreach ($tids as $tid) {
        \Drupal::database()->insert("taxonomy_term__{$field_name}")
          ->fields([
            'bundle' => 'acq_product_category',
            'deleted' => 0,
            'entity_id' => $tid,
            'revision_id' => $tid,
            'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
            'delta' => 0,
            "{$field_name}_value" => TRUE,
          ])
          ->execute();
      }
    }
  }
}
