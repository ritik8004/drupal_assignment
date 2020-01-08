<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Script to reset meta tags overrides for categories.
 */

$terms = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties([
    'vid' => 'acq_product_category',
  ]);

foreach ($terms as $term) {
  $term->get('field_meta_tags')->setValue(serialize([]));
  $term->save();
}
