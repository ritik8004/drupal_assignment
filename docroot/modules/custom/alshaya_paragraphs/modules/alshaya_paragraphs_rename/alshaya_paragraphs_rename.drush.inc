<?php

/**
 * @file
 * Drush file for alshaya_paragraphs_rename.
 */

/**
 * Implements hook_drush_command().
 */
function alshaya_paragraphs_rename_drush_command() {
  $commands = [];

  $commands['alshaya-paragraphs-rename'] = [
    'description' => 'Rename alshaya paragraphs.',
    'aliases' => ['apr'],
    'arguments' => [
      'current_name' => 'Current bundle name.',
      'new_name' => 'New bundle name.',
    ],
    'required-arguments' => TRUE,
    'examples' => [
      'rename paragraph bundle' => 'drush alshaya-paragraphs-rename old_bundle_name new_bundle_name',
    ],
  ];

  return $commands;
}

/**
 * Rename paragraphs bundle.
 *
 * @param string $current_name
 *   Existing bundle name.
 * @param string $new_name
 *   New bundle name.
 */
function drush_alshaya_paragraphs_rename($current_name, $new_name) {
  $paragraph_rename = \Drupal::service('alshaya_paragraphs_rename');
  $paragraph_rename->renameParagraphBundle($current_name, $new_name);

  $msg = dt('All the paragraph items are renamed, Do you want to delete bundle with old name?');
  if (drush_confirm($msg)) {
    $result = $paragraph_rename->deleteParagraphBundle($current_name);
    drush_print($result);
  }
  else {
    return drush_user_abort();
  }
}
