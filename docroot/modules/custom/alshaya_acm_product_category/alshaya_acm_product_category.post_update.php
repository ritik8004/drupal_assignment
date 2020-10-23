<?php

/**
 * @file
 * Post update functions for alshaya_acm_product_category.
 */

/**
 * Set default values for newly created fields.
 */
function alshaya_acm_product_category_post_update_8032() {
  $termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
  $query = $termStorage->getQuery();
  $query->condition('field_include_in_desktop', 0);
  $query->condition('field_include_in_mobile_tablet', 0);
  $query->exists('field_commerce_id');
  $terms = $query->execute();

  foreach ($terms as $tid) {
    $term = $termStorage->load($tid);
    $term->get('field_include_in_desktop')->setValue(1);
    $term->get('field_include_in_mobile_tablet')->setValue(1);
    $term->save();
  }
}

/**
 * Set default values for newly created fields.
 */
function alshaya_acm_product_category_post_update_8030() {
  // Set include in desktop and mobile to true, to make existing links visible
  // on all devices as is.
  foreach ([
    'field_include_in_desktop',
    'field_include_in_mobile_tablet',
  ] as $field_name) {
    $query = \Drupal::database()->select('taxonomy_term_field_data', 'ttfd');
    $query->leftJoin("taxonomy_term__{$field_name}", 'tb', 'ttfd.tid = tb.entity_id');
    $query->fields('ttfd', ['tid', 'langcode']);
    $query->condition('ttfd.vid', 'acq_product_category');
    $query->condition('ttfd.default_langcode', 1);
    $query->condition("tb.{$field_name}_value", '', 'IS NULL');
    $results = $query->execute()->fetchAll();

    if (!empty($results)) {
      foreach ($results as $result) {
        \Drupal::database()->insert("taxonomy_term__{$field_name}")
          ->fields([
            'bundle' => 'acq_product_category',
            'deleted' => 0,
            'entity_id' => $result->tid,
            'revision_id' => $result->tid,
            'langcode' => $result->langcode,
            'delta' => 0,
            "{$field_name}_value" => TRUE,
          ])
          ->execute();
      }
    }
  }
}
