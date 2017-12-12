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

/**
 * Insert data at position given the target key.
 *
 * @param array $array
 *   Array to process.
 * @param mixed $target_key
 *   Target key to check.
 * @param mixed $insert_key
 *   Key for the new value.
 * @param mixed $insert_val
 *   New value to insert.
 * @param bool $insert_after
 *   Flag to specify if we want to insert after or before.
 * @param bool $append_on_fail
 *   Append if not able to find target key.
 *
 * @return array
 *   Updated array.
 */
function _alshaya_array_insert(array $array, $target_key, $insert_key, $insert_val = NULL, $insert_after = TRUE, $append_on_fail = FALSE) {
  $out = [];

  foreach ($array as $key => $value) {
    if ($insert_after) {
      $out[$key] = $value;
    }
    if ($key == $target_key) {
      $out[$insert_key] = $insert_val;
    }
    if (!$insert_after) {
      $out[$key] = $value;
    }
  }

  if (!isset($array[$target_key]) && $append_on_fail) {
    $out[$insert_key] = $insert_val;
  }

  return $out;
}
