<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$settings['checkout_com_accepted_cards'] = [
  'visa',
  'mastercard',
  'diners',
];

$settings['checkout_com_upapi_accepted_cards_mapping'] = [
  'visa' => 'visa',
  'mastercard' => 'mastercard',
  'Diners Club International' => 'diners',
  'AMERICAN EXPRESS' => 'amex',
];
