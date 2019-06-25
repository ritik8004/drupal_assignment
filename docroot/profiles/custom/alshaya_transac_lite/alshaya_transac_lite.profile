<?php

/**
 * @file
 * Enables modules and site configuration for the alshaya_transac_lite profile.
 */

use Drupal\Core\Form\FormStateInterface;

require_once __DIR__ . '/../alshaya/alshaya.profile';

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function alshaya_transac_lite_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  alshaya_form_install_configure_form_alter($form, $form_state);
}
