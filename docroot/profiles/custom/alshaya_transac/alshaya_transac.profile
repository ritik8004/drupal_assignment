<?php

/**
 * @file
 * Enables modules and site configuration for the alshaya_transac profile.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function alshaya_transac_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  // We don't want the update module to be enabled.
  unset($form['update_notifications']);
}
