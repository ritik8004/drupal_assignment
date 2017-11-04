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

  $commands['alshaya-api-sync-areas'] = [
    'description' => 'Imports all areas into Drupal using direct Magento API.',
    'aliases'     => ['aasa'],
  ];

  return $commands;
}

/**
 * Implements drush store sync command.
 */
function drush_alshaya_api_sync_stores() {
  drush_print(dt('Synchronizing all stores, please wait...'));
  \Drupal::service('alshaya_api.api')->syncStores();
  \Drupal::logger('alshaya_api')->notice('Store sync completed.');
  drush_print(dt('Done.'));
}

/**
 * Implements drush areas sync command.
 */
function drush_alshaya_api_sync_areas() {
  drush_print(dt('Synchronizing all areas, please wait...'));
  // Set DM version to 2.
  \Drupal::configFactory()->getEditable('alshaya_api.settings')
    ->set('dm_version', 2)
    ->save();
  // Import DM data.
  \Drupal::service('alshaya_api.api')->syncAreas();
  \Drupal::logger('alshaya_api')->notice('Area sync completed.');
  drush_print(dt('Done.'));
}
