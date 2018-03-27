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
  // Get editable category_menu settings.
  $config = \Drupal::configFactory()->getEditable('alshaya_acm_product_category.category_menu.settings');

  $status = $config->get('category_menu_top_level') ? FALSE : TRUE;
  $action = $status ? 'enable' : 'disabled';

  if (!drush_confirm(dt('Are you sure you want to !action super category feature?', ['!action' => $action]))) {
    return drush_user_abort();
  }

  // Determine which default parent to use for main menu.
  $default_parent = $status ? drush_get_option('default_parent', '') : '';

  // Update settings for category_menu.
  $config = \Drupal::configFactory()->getEditable('alshaya_acm_product_category.category_menu.settings');
  $config->set('category_menu_top_level', $status);
  if (empty($default_parent) && $status) {
    $terms = \Drupal::service('alshaya_main_menu.product_category_tree')->getCategoryRootTerms();
    $default_parent = key($terms);
  }
  $config->set('category_default_parent', $default_parent);
  $config->save(TRUE);

  // Load super category menu block and set active.
  $blocks = \Drupal::entityTypeManager()->getStorage('block')->loadByProperties(['id' => 'supercategorymenu']);
  $block = reset($blocks);
  $block->setStatus($status);
  $block->save();

  drush_print(dt('Successfully !action super category feature.', ['!action' => $action . 'd']));
}
