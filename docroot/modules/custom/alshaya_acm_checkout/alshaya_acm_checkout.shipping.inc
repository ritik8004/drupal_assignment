<?php

/**
 * @file
 * Utility functions related to shipping methods for alshaya_acm_checkout.
 */

/**
 * Function to load or create shipping method term from code.
 *
 * @param string $code
 *   Shipping method code.
 * @param string $name
 *   Name of shipping method, available during checkout.
 *
 * @return \Drupal\Core\Entity\EntityInterface|null
 *   Loaded term object.
 */
function alshaya_acm_checkout_load_shipping_method($code, $name = '') {
  $save_term = FALSE;
  $logger = \Drupal::logger('alshaya_acm_checkout');
  $taxonomy_term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

  $query = \Drupal::entityQuery('taxonomy_term');
  $query->condition('vid', 'shipping_method');
  $query->condition('field_shipping_method_code', $code);

  $result = $query->execute();

  if (empty($result)) {
    if (empty($name)) {
      $name = $code;
    }

    $term = $taxonomy_term_storage->create([
      'vid' => 'shipping_method',
      'name' => $name,
    ]);

    $term->get('description')->setValue($name);
    $term->get('field_shipping_method_desc')->setValue($name);
    $term->get('field_shipping_method_code')->setValue($code);

    $save_term = TRUE;

    $logger->critical('New shipping method created for code @code. Please save the description asap.', ['@code' => $code]);
  }
  else {
    if (count($result) > 1) {
      $logger->error('Duplicate shipping method terms found for code @code.', ['@code' => $code]);
    }

    $tid = array_shift($result);

    /** @var \Drupal\taxonomy\Entity\Term $term */
    $term = $taxonomy_term_storage->load($tid);

    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if ($term->hasTranslation($langcode)) {
      $term = $term->getTranslation($langcode);
    }
  }

  // Save the term only if required.
  if ($save_term) {
    $term->save();
  }

  return $term;
}
