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

  $commands['alshaya-category-top-menu'] = [
    'description' => 'Enable top level category module.',
    'aliases' => ['actm'],
    'options' => [
      'default_parent' => 'Default parent for main menu',
    ],
  ];

  return $commands;
}

/**
 * Implements drush command alshaya-category-top-menu.
 */
function drush_alshaya_acm_product_category_alshaya_category_top_menu() {
  // Determine which default parent to use for main menu.
  $default_parent = drush_get_option('default_parent', '');

  // Update settings for category_menu.
  $config = \Drupal::configFactory()->getEditable('alshaya_acm_product_category.category_menu.settings');
  $config->set('category_menu_top_level', TRUE);
  if (empty($default_parent)) {
    $terms = \Drupal::service('alshaya_main_menu.product_category_tree')->getCategoryRootTerms();
    $default_parent = key($terms);
  }
  $config->set('category_default_parent', $default_parent);
  $config->save(TRUE);

  // Load super category menu block and set active.
  $blocks = \Drupal::entityTypeManager()->getStorage('block')->loadByProperties(['id' => 'supercategorymenu']);
  $block = reset($blocks);
  $block->setStatus(TRUE);
  $block->save();

  drush_print(dt('Enabled top level category menu block.'));
}
