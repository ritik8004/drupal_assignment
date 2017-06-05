<?php

/**
 * @file
 * Provides Drush commands for Alshaya i18n.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_i18n_drush_command() {
  $commands = [];

  $commands['alshaya-i18n-onetime-translation-add'] = [
    'description' => 'Import translaton from the codebase post drush si.',
    'aliases' => ['aiota'],
  ];

  return $commands;
}

/**
 * Implements drush onetime translation add command.
 */
function drush_alshaya_i18n_onetime_translation_add() {
  $sync_status = \Drupal::state()->get('alshaya_i18n_onetime_translation_add', 'not done');

  if ($sync_status == 'not done') {
    drush_print(dt('Synchronizing translations, please wait...'));

    // Invoke a hook to allow all modules to add translations post install.
    \Drupal::moduleHandler()->invokeAll('alshaya_i18n_onetime_translation_add');

    // Set the state variable to done to avoid redoing it.
    \Drupal::state()->set('alshaya_i18n_onetime_translation_add', 'done');

    drush_print(dt('Done.'));
  }
  else {
    drush_print(dt('Sync was already done before, not doing again.'));
  }
}
