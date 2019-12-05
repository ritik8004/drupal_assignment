<?php

/**
 * @file
 * Enables modules and site configuration for the alshaya profile.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use Drupal\node\Entity\NodeType;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function alshaya_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  // We don't want the update module to be enabled.
  unset($form['update_notifications']);
}

/**
 * Helper function to know if env is prod or not.
 *
 * @return bool
 *   True if env if prod.
 */
function alshaya_is_env_prod() {
  $env = Settings::get('env') ?: 'local';

  // @TODO: Find a better way to check if env is prod.
  return preg_match('/\d{2}(live|update)/', $env);
}

/**
 * Implements hook_alshaya_transac_profile_installed().
 *
 * This is not real hook implementation as we are in profile file. This
 * function is called at the end of alshaya_transac.install.
 */
function alshaya_sub_profile_installed($profile) {
  alshaya_final_common_install_task($profile);
}

/**
 * This is the very last function called at the end of the installation.
 */
function alshaya_final_common_install_task($profile) {
  global $_alshaya_modules_installed;

  // Get the modules to be enabled for this env.
  $additional_modules = Settings::get('additional_modules');

  // Additional modules which are to be enabled are to be done here
  // as it cant be done in the hook_install.
  if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
    $additional_modules[] = 'shield';
    $additional_modules[] = 'acquia_purge';
  }

  foreach ($additional_modules as $module) {
    \Drupal::service('module_installer')->install([$module]);
  }

  if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
    // Enable acquia_purge purger.
    /** @var \Drupal\purge\Plugin\Purge\Purger\PurgersServiceInterface $purge_purgers */
    $purge_purgers = \Drupal::service('purge.purgers');
    $purgers_enabled = $purge_purgers->getPluginsEnabled();
    $purgers_enabled[$purge_purgers->createId()] = 'acquia_purge';
    $purge_purgers->setPluginsEnabled($purgers_enabled);
  }

  if (\Drupal::moduleHandler()->moduleExists('shield')) {
    \Drupal::getContainer()->get('config.factory')
      ->getEditable('shield.settings')
      ->set('allow_cli', TRUE)
      ->set('user', Settings::get('alshaya_custom_shield_default_user'))
      ->set('pass', Settings::get('alshaya_custom_shield_default_pass'))
      ->set('print', '')
      ->save();
  }

  \Drupal::moduleHandler()->invokeAll('alshaya_profile_installed_final_task', [$profile, $_alshaya_modules_installed]);

  // Delete basic page content type, we don't need this in Alshaya.
  $node_type = NodeType::load('page');
  if ($node_type) {
    $node_type->delete();
  }
}
