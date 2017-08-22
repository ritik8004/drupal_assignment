<?php

/**
 * @file
 * Enables modules and site configuration for the Lightning Extender profile.
 */

use Drupal\Core\Site\Settings;

/**
 * Implements hook_alshaya_transac_profile_installed().
 *
 * This is not real hook implementation as we are in profile file. This
 * function is called at the end of alshaya_transac.install.
 */
function alshaya_alshaya_transac_profile_installed($modules) {
  alshaya_final_common_install_task();
}

/**
 * Implements hook_alshaya_non_transac_profile_installed().
 *
 * This is not real hook implementation as we are in profile file. This
 * function is called at the end of alshaya_non_transac.install.
 */
function alshaya_alshaya_non_transac_profile_installed($modules) {
  alshaya_final_common_install_task();
}

/**
 * This is the very last function called at the end of the installation.
 */
function alshaya_final_common_install_task() {
  // Enable shield on ACSF environments.
  if (isset($_ENV['AH_SITE_NAME'])) {
    \Drupal::service('module_installer')->install(['shield']);

    \Drupal::getContainer()->get('config.factory')
      ->getEditable('shield.settings')
      ->set('allow_cli', TRUE)
      ->set('user', Settings::get('alshaya_custom_shield_default_user'))
      ->set('pass', Settings::get('alshaya_custom_shield_default_pass'))
      ->set('print', '')
      ->save();
  }
}
