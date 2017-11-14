<?php

/**
 * @file
 * Provides Drush commands for Acquia Commerce Middleware related activities.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_addressbook_drush_command() {
  $commands = [];

  $commands['alshaya-addressbook-sync-areas'] = [
    'description' => 'Imports all areas into Drupal using direct Magento API.',
    'aliases'     => ['aasa'],
  ];

  return $commands;
}

/**
 * Implements drush areas sync command.
 */
function drush_alshaya_addressbook_sync_areas() {
  if (\Drupal::config('alshaya_addressbook.settings')->get('dm_version') == 2) {
    drush_print(dt('Synchronizing all areas, please wait...'));
    // Import DM data.
    \Drupal::service('alshaya_addressbook.manager')->syncAreas();
    \Drupal::logger('alshaya_addressbook')->notice('Area sync completed.');
    drush_print(dt('Done.'));
  }
  else {
    drush_print(dt('Incorrect DM version for this command.'));
  }

}
