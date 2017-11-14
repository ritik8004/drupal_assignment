<?php

/**
 * @file
 * Enables modules and site configuration for the alshaya profile.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;

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
  $prod_envs = [
    '01live',
    '01update',
  ];

  return in_array($env, $prod_envs);
}
