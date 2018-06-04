<?php

/**
 * @file
 * Provides Drush commands for Acquia Commerce Middleware related activities.
 */

use Drupal\alshaya_addressbook\AlshayaAddressBookManagerInterface;

/**
 * Implements hook_drush_command().
 */
function alshaya_addressbook_drush_command() {
  $commands = [];

  $commands['alshaya-addressbook-sync-areas'] = [
    'description' => 'Imports all areas into Drupal using direct Magento API.',
    'aliases'     => ['aasa', 'sync-areas'],
  ];

  $commands['alshaya-addressbook-sync-form-fields'] = [
    'description' => 'Syncs and resets form fields cache.',
    'aliases'     => ['aasff', 'sync-form-fields'],
  ];

  return $commands;
}

/**
 * Implements drush alshaya-addressbook-sync-areas command.
 */
function drush_alshaya_addressbook_sync_areas() {
  /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $addressbook_manager */
  $addressbook_manager = \Drupal::service('alshaya_addressbook.manager');

  // DM Version check.
  if ($addressbook_manager->getDmVersion() != AlshayaAddressBookManagerInterface::DM_VERSION_2) {
    \Drupal::logger('alshaya_addressbook')->error('Incorrect DM version for this command.');
    return;
  }

  // Update the area list.
  \Drupal::logger('alshaya_addressbook')->notice('Synchronizing all areas, please wait...');

  // Import DM data.
  $addressbook_manager->syncAreas();

  \Drupal::logger('alshaya_addressbook')->notice('Done.');
}

/**
 * Implements drush alshaya-addressbook-sync-form-fields command.
 */
function drush_alshaya_addressbook_sync_form_fields() {
  /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $addressbook_manager */
  $addressbook_manager = \Drupal::service('alshaya_addressbook.manager');

  // DM Version check.
  if ($addressbook_manager->getDmVersion() != AlshayaAddressBookManagerInterface::DM_VERSION_2) {
    \Drupal::logger('alshaya_addressbook')->error('Incorrect DM version for this command.');
    return;
  }

  // Reset magento form fields cache.
  $addressbook_manager->resetMagentoFormFields();
  \Drupal::logger('alshaya_addressbook')->notice('Form fields cache reset.');
}
