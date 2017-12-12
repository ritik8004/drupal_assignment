<?php

/**
 * @file
 * Enables modules and site configuration for the alshaya_transac profile.
 */

use Drupal\Core\Form\FormStateInterface;

require_once __DIR__ . '/../alshaya/alshaya.profile';

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function alshaya_transac_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  alshaya_form_install_configure_form_alter($form, $form_state);
}

/**
 * Function to return currency code for current country in requested language.
 *
 * @param string $country_code
 *   Country code.
 * @param string $lang_code
 *   Language code.
 *
 * @return string
 *   Currency code.
 */
function _alshaya_transac_get_currency_code($country_code, $lang_code) {
  $country_code = strtolower($country_code);
  $lang_code = strtolower($lang_code);
  $currency = [];

  // KW.
  $currency['kw']['en'] = 'KWD';
  $currency['kw']['ar'] = 'د٠ك٠';

  // KSA.
  $currency['sa']['en'] = 'SR';
  $currency['sa']['ar'] = '.ر.س';

  // UAE.
  $currency['ae']['en'] = 'AED';
  $currency['ae']['ar'] = '.ر.إ';

  return $currency[$country_code][$lang_code];
}
