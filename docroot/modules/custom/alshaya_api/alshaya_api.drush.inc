<?php

/**
 * @file
 * Provides Drush commands for Acquia Commerce Middleware related activities.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_api_drush_command() {
  $commands = [];

  $commands['alshaya-api-sync-stores'] = [
    'description' => 'Imports all stores into Drupal using direct Magento API.',
    'aliases'     => ['aass'],
  ];

  return $commands;
}

/**
 * Implements drush categories sync command.
 */
function drush_alshaya_api_sync_stores() {
  drush_print(dt('Synchronizing all stores, please wait...'));
  \Drupal::service('alshaya_api.api')->syncStores();
  \Drupal::logger('alshaya_api')->notice('Store sync completed.');
  drush_print(dt('Done.'));
}
