<?php

/**
 * @file
 * Provides Drush commands for alshaya_master.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_master_drush_command() {
  $commands = [];

  $commands['alshaya-post-drupal-install'] = [
    'description' => 'Code to be executed only once post install.',
    'aliases' => ['apdi'],
  ];

  return $commands;
}

/**
 * Implements drush command alshaya-post-drupal-install.
 */
function drush_alshaya_master_alshaya_post_drupal_install() {
  $post_install_status = \Drupal::state()->get('alshaya_master_post_drupal_install', 'not done');

  if ($post_install_status == 'not done') {
    drush_print(dt('Executing post install code, please wait...'));

    // Invoke a hook to allow all modules to run code once post install.
    \Drupal::moduleHandler()->invokeAll('alshaya_master_post_drupal_install');

    // Set the state variable to done to avoid redoing it.
    \Drupal::state()->set('alshaya_master_post_drupal_install', 'done');

    drush_print(dt('Done.'));
  }
  else {
    drush_print(dt('Post install code executed already, not doing again.'));
  }
}
