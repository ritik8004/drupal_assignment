<?php

/**
 * @file
 * Script to fix attribute values in product_list node translations.
 *
 * Use this script using drush php-script. Should be executed from
 * docroot folder.
 *
 * E.g. `drush php-script scripts/utilities/product_list_fix_translations.php`
 */

use Drupal\acq_sku\ProductOptionsManager;

$time_start = microtime(TRUE);

$logger = \Drupal::logger('product_list_fix_translations');

$node_storage = \Drupal::entityTypeManager()->getStorage('node');
$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

$nids = $node_storage->getQuery()
  ->condition('type', 'product_list')
  ->execute();

foreach ($nids as $nid) {
  $node = $node_storage->load($nid);

  $logger->notice('Processing for @nid @title', [
    '@nid' => $node->id(),
    '@title' => $node->label(),
  ]);

  if ($node->hasTranslation('ar')) {
    $node_ar = $node->getTranslation('ar');
    $attribute_value = $node_ar->get('field_attribute_value')->getString();
    $attribute_name = $node_ar->get('field_attribute_name')->getString();

    $logger->notice('Attribute value @value', [
      '@value' => $attribute_value,
    ]);

    if (!preg_match("/\p{Arabic}/u", $attribute_value)) {
      $term_ids = $term_storage->getQuery()
        ->condition('name', $attribute_value)
        ->condition('field_sku_attribute_code', str_replace('attr_', '', $attribute_name))
        ->condition('vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY)
        ->execute();

      if (empty($term_ids)) {
        continue;
      }

      $term_id = reset($term_ids);
      $term = $term_storage->load($term_id);
      $term = $term->getTranslation('ar');
      $node_ar->get('field_attribute_value')->setValue($term->label());
      $node_ar->set('title', $term->label());
      $node_ar->save();
      $logger->notice('Fixed translation for @nid', [
        '@nid' => $node->id(),
      ]);
    }
  }
}

$time_end = microtime(TRUE);

$logger->notice(dt('Finished process. Total time taken: @time', [
  '@time' => round(($time_end - $time_start), 0) . ' seconds',
]));
