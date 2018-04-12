<?php

/**
 * @file
 * Provides Drush commands for alshaya_promo_panel.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_promo_panel_drush_command() {
  $commands = [];

  $commands['alshaya-promo-panel-set'] = [
    'description' => 'config promo panel block and path.',
    'aliases' => ['apps'],
    'options' => [
      'blocks' => 'Default block machine name to use for promo panel',
    ],
  ];

  return $commands;
}

/**
 * Implements drush command alshaya-promo-panel-set.
 */
function drush_alshaya_promo_panel_set() {
  $block_names = drush_get_option_list('blocks', []);

  if (empty($block_names)) {
    drush_print(dt('Please pass the --blocks argument to set block as promo panel.'));
    return;
  }

  $config = \Drupal::configFactory()->getEditable('alshaya_promo_panel.settings');
  $new_array = [];

  // Set multiple blocks as key and value array.
  array_map(function ($block_name) use (&$new_array) {
    list($block, $url) = explode(':', $block_name);
    $new_array[$block] = $url;
  }, $block_names);
  $config->set('promo_panel_blocks', $new_array);
  $config->save();

  // Enable the blocks.
  $storage = \Drupal::entityTypeManager()->getStorage('block');
  $query = $storage->getQuery()->condition('id', array_keys($new_array), 'IN');
  $blocks = $storage->loadMultiple($query->execute());
  foreach ($blocks as $block) {
    if ($block->status()) {
      continue;
    }
    $block->setStatus(TRUE);
    $block->save();
  }

  drush_print(dt('Successfully updated promo panel config.'));
}
