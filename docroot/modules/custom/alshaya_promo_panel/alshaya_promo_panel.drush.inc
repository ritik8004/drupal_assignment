<?php

/**
 * @file
 * Provides Drush commands for alshaya_promo_panel.
 */

use Drupal\block\BlockInterface;

/**
 * Implements hook_drush_command().
 */
function alshaya_promo_panel_drush_command() {
  $commands = [];

  $commands['alshaya-promo-panel-set'] = [
    'description' => 'config promo panel block and path.',
    'aliases' => ['apps'],
    'arguments' => [
      'blocks' => 'Block with url value. i.e. alshaya_promo_panel:/todays-offer',
    ],
    'examples' => [
      'drush apps alshaya_promo_panel:/todays-offer' => 'Single input to set block and url for mobile.',
      'drush apps alshaya_promo_panel:/todays-offer,alshaya_custom:/about' => 'Comma separated input to set multiple blocks.',
    ],
  ];

  return $commands;
}

/**
 * Implements drush command alshaya-promo-panel-set.
 */
function drush_alshaya_promo_panel_set($blocks) {
  if (empty($blocks)) {
    return drush_set_error(dt('blocks is required argument. i.e. drush apps alshaya_promo_panel:/todays-offer,alshaya_custom:/about'));
  }

  // Clean input and parse comma-separated input items into array.
  $block_names = explode(',', $blocks);
  $block_storage = \Drupal::entityTypeManager()->getStorage('block');
  $promo_panel_blocks = [];
  array_map(function ($block_name) use (&$promo_panel_blocks, $block_storage) {
    list($block_machine_name, $mobile_path) = explode(':', $block_name);
    $block_results = $block_storage->loadByProperties(['id' => $block_machine_name]);
    $block = reset($block_results);
    if ($block instanceof BlockInterface) {
      $promo_panel_blocks[$block_machine_name] = [
        'mobile_path' => $mobile_path,
        'plugin_id' => $block->getPluginId(),
      ];
    }
  }, $block_names);

  $config = \Drupal::configFactory()->getEditable('alshaya_promo_panel.settings');
  $config->set('promo_panel_blocks', $promo_panel_blocks);
  $config->save();

  // Enable the blocks if disabled.
  $storage = \Drupal::entityTypeManager()->getStorage('block');
  $query = $storage->getQuery()->condition('id', array_keys($promo_panel_blocks), 'IN');
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
