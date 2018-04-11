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
      'block' => 'Default block machine name to use for promo panel',
      'url' => 'url for promo panel in mobile.',
    ],
  ];

  return $commands;
}

/**
 * Implements drush command alshaya-promo-panel-set.
 */
function drush_alshaya_promo_panel_set() {
  $config = \Drupal::configFactory()->getEditable('alshaya_promo_panel.settings');

  $block_name = drush_get_option('block', 0);
  $page_url = drush_get_option('url', 0);

  if (!empty($block_name)) {
    $config->set('promo_panel_block', $block_name);
    // Get the block content machine name from the block and set it in config.
    $blocks = \Drupal::entityTypeManager()->getStorage('block')->loadByProperties(['id' => $block_name]);
    $block = reset($blocks);
    if (strpos($block->getPluginId(), 'block_content') !== FALSE) {
      $uuid = explode('.', $block->getPluginId())[1];
      $block_contents = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['uuid' => $uuid]);
      $block_content = reset($block_contents);
      $machine_name = $block_content->get('machine_name')->value;
      $config->set('promo_panel_block_content', $machine_name);
    }
  }

  if (!empty($page_url) && \Drupal::service('path.validator')->isValid($page_url)) {
    $config->set('promo_panel_page_url', $page_url);
  }

  $config->save(TRUE);

  drush_print(dt('Successfully updated promo panel config.'));
}
