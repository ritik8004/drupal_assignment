<?php

/**
 * @file
 * Provides Drush commands for alshaya_acm_product_category.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_acm_product_category_drush_command() {
  $commands = [];

  $commands['alshaya-super-category-switch'] = [
    'description' => 'Enable or disable super category feature.',
    'aliases' => ['ascs'],
    'options' => [
      'default_parent' => 'Default parent term id to render main menu',
    ],
  ];

  return $commands;
}

/**
 * Implements drush command alshaya-super-category-switch.
 */
function drush_alshaya_acm_product_category_alshaya_super_category_switch() {
  // Get editable alshaya_acm_product_category.super_category.settings.
  $config = \Drupal::configFactory()->getEditable('alshaya_acm_product_category.super_category.settings');

  $status = $config->get('status') ? FALSE : TRUE;
  $action = $status ? 'enable' : 'disabled';

  if (!drush_confirm(dt('Are you sure you want to !action super category feature?', ['!action' => $action]))) {
    return drush_user_abort();
  }

  // @todo: Validate the given default_parent exists and from the appropriate
  // vocabulary and is rootTerm.
  // Determine which default parent to use for main menu.
  $default_parent = drush_get_option('default_parent', 0);

  // Update alshaya_acm_product_category.super_category.settings.
  $config = \Drupal::configFactory()->getEditable('alshaya_acm_product_category.super_category.settings');
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

  drush_print(dt('Successfully !action super category feature.', ['!action' => $action . 'd']));
}
