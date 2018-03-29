<?php

/**
 * @file
 * Provides Drush commands for alshaya_stores_finder_transac.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_stores_finder_transac_drush_command() {
  $commands = [];

  $commands['alshaya-stores-finder-transac-sync-stores'] = [
    'description' => 'Imports all stores into Drupal using direct Magento API.',
    'aliases'     => ['aass', 'sync-stores'],
  ];

  return $commands;
}

/**
 * Implements drush store sync command.
 */
function drush_alshaya_stores_finder_transac_sync_stores() {
  drush_print(dt('Synchronizing all stores, please wait...'));

  /** @var \Drupal\alshaya_stores_finder_transac\StoresFinderManager $manager */
  $manager = \Drupal::service('alshaya_stores_finder_transac.manager');

  $manager->syncStores();
}
