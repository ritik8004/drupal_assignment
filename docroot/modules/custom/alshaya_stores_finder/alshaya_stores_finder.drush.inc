<?php

/**
 * @file
 * Provides Drush commands for Acquia Commerce Middleware related activities.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_stores_finder_drush_command() {
  $commands = [];

  $commands['alshaya-stores-finder-sync-stores'] = [
    'description' => 'Imports all stores into Drupal using direct Magento API.',
    'aliases'     => ['asfss'],
  ];

  return $commands;
}

/**
 * Implements drush categories sync command.
 */
function drush_alshaya_stores_finder_sync_stores() {
  drush_print(dt('Synchronizing all stores, please wait...'));

  \Drupal::service('alshaya_stores_finder.utility')->syncStores();

  drush_print(dt('Done.'));
}
