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
  $new_array = [];
  array_map(function ($block_name) use (&$new_array, $block_storage) {
    list($block, $url) = explode(':', $block_name);
    $blocks_array = $block_storage->loadByProperties(['id' => $block]);
    $block_obj = reset($blocks_array);
    if ($block_obj instanceof BlockInterface) {
      $new_array[$block] = [
        'mobile_path' => $url,
        'plugin_id' => $block_obj->getPluginId(),
      ];
    }
  }, $block_names);

  $config = \Drupal::configFactory()->getEditable('alshaya_promo_panel.settings');
  $config->set('promo_panel_blocks', $new_array);
  $config->save();

  // Enable the blocks if disabled.
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
