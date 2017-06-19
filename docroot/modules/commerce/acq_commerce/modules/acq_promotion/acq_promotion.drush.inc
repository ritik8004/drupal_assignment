<?php

/**
 * @file
 * Provides drush commands for the promotions of Acquia Commerce connector.
 */

/**
 * Implements hook_drush_command().
 */
function acq_promotion_drush_command() {
  $commands = [];

  $commands['sync-commerce-promotions'] = [
    'description' => 'Run a full synchronization of all commerce promotion records.',
    'aliases'     => ['acspm'],
    'examples'    => [
      'drush acspm' => 'Run a full synchronization of all available promotions.',
    ],
  ];

  return ($commands);
}

/**
 * Implements drush product sync command.
 */
function drush_acq_promotion_sync_commerce_promotions() {
  drush_print(dt('Synchronizing all commerce promotions, this usually takes some time...'));
  \Drupal::service('acq_promotion.promotions_manager')->syncPromotions();
  drush_print(dt('Done.'));
}
