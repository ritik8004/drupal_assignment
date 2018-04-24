<?php

/**
 * @file
 * Provides Drush commands for alshaya_super_category.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_super_category_drush_command() {
  $commands = [];

  $commands['alshaya-super-category-switch'] = [
    'description' => 'Enable or disable super category feature.',
    'aliases' => ['ascs'],
    'options' => [
      'default_parent' => 'Default parent term id to render main menu',
    ],
  ];

  $commands['alshaya-super-category-product-alias'] = [
    'description' => 'Enable or disable product alias alter based on super category status.',
    'aliases' => ['ascs-pa'],
  ];

  return $commands;
}

/**
 * Implements drush command alshaya-super-category-switch.
 */
function drush_alshaya_super_category_switch() {
  $config = \Drupal::configFactory()->getEditable('alshaya_super_category.settings');

  $status = $config->get('status') ? FALSE : TRUE;
  $action = $status ? 'enable' : 'disable';

  $msg = dt('Are you sure you want to !action super category feature?', ['!action' => $action]);

  // Check path alter status to display message and trigger bulk alias generate.
  $path_alter = $config->get('product_path_alter', TRUE);
  if ($path_alter && 'enable' == $action) {
    $msg = dt('Are you sure you want to !action super category feature and do bulk update on product aliases', ['!action' => $action]);
  }

  if (!drush_confirm($msg)) {
    return drush_user_abort();
  }

  // @todo: Validate the given default_parent exists and from the appropriate
  // vocabulary and is rootTerm.
  // Determine which default parent to use for main menu.
  $default_parent = drush_get_option('default_parent', 0);

  // Update alshaya_super_category.settings.
  $config->set('status', $status);
  if ($status) {
    if (empty($default_parent)) {
      $terms = \Drupal::service('alshaya_acm_product_category.product_category_tree')->getCategoryRootTerms();
      $default_parent = key($terms);
    }
    $config->set('default_category_tid', $default_parent);
  }
  $config->save(TRUE);

  // Load super category menu block and change status.
  $blocks = \Drupal::entityTypeManager()->getStorage('block')->loadByProperties(['id' => 'supercategorymenu']);
  $block = reset($blocks);
  $block->setStatus($status);
  $block->save();
  if ($path_alter) {
    _alshaya_super_category_product_alias_bulk_process();
  }
  \Drupal::moduleHandler()->invokeAll('alshaya_super_category_status_update', [
    $status,
    $default_parent,
    $path_alter,
  ]);

  // Clear cache to rebuild menu blocks.
  drupal_flush_all_caches();
  drush_print(dt('Successfully !action super category feature.', ['!action' => $action . 'd']));
}

/**
 * Implements drush command alshaya-super-category-product-alias (ascs-pa).
 */
function drush_alshaya_super_category_product_alias() {
  $config = \Drupal::configFactory()->getEditable('alshaya_super_category.settings');
  $status = $config->get('product_path_alter') ? FALSE : TRUE;
  $action = $status ? 'true' : 'false';

  if (!drush_confirm(dt('Are you sure you want to set product alias alter to !value, for super category feature?', ['!value' => $action]))) {
    return drush_user_abort();
  }

  $config->set('product_path_alter', $status)->save();
  _alshaya_super_category_product_alias_bulk_process();
}

/**
 * Product url alias bulk process.
 */
function _alshaya_super_category_product_alias_bulk_process() {
  drush_print(dt('Generating product aliases, please wait...'));

  // Set batch operation.
  $batch = [
    'title' => t('Bulk updating product URL aliases'),
    'init_message' => t('Product alias generating...'),
    'operations' => [
      ['\Drupal\pathauto\Form\PathautoBulkUpdateForm::batchStart', []],
      ['\Drupal\pathauto\Form\PathautoBulkUpdateForm::batchProcess',
        ['canonical_entities:node', 'all'],
      ],
    ],
    'progress_message' => t('Processed @current out of @total.'),
    'error_message' => t('Synced data could not be cleaned because an error occurred.'),
    // Drush doesn't support static method call for "finished".
    'finished' => '_alshaya_super_category_product_alias_batch_finished',
  ];

  batch_set($batch);
  drush_backend_batch_process();
}

/**
 * Batch finished callback.
 *
 * @param bool $success
 *   Whether the batch job was successful.
 * @param \Drupal\security_review\CheckResult[] $results
 *   The results of the batch job.
 * @param array $operations
 *   The array of batch operations.
 */
function _alshaya_super_category_product_alias_batch_finished($success, array $results, array $operations) {
  if ($success) {
    if ($results['updates']) {
      drush_print(\Drupal::translation()->formatPlural($results['updates'], 'Generated 1 URL alias.', 'Generated @count URL aliases.'));
    }
    else {
      drush_print(dt('No new URL aliases to generate.'));
    }
  }
  else {
    $error_operation = reset($operations);
    drush_print(dt('An error occurred while processing @operation with arguments : @args', [
      '@operation' => $error_operation[0],
      '@args' => print_r($error_operation[0], TRUE),
    ]));
  }
}
