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
 * Implements drush store sync command.
 */
function drush_alshaya_api_sync_stores() {
  drush_print(dt('Synchronizing all stores, please wait...'));

  /** @var \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper */
  $api_wrapper = \Drupal::service('alshaya_api.api');

  if ($api_wrapper->syncStores()) {
    \Drupal::logger('alshaya_api')->notice('Stores sync completed.');
  }
  else {
    \Drupal::logger('alshaya_api')->error('Either no stores found or error occurred while doing stores sync. Please check logs for more details.');
  }
}
