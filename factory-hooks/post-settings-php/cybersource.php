<?php

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$settings['cybersource']['env'] = 'test';

// We usually connect PPROD to PROD, so live mode on PPROD and PROD.
$env = alshaya_get_site_environment();
if (preg_match('/\d{2}(live|update|pprod)/', $env)) {
  $settings['cybersource']['env'] = 'prod';
}

$settings['cybersource']['url']['test'] = 'https://testsecureacceptance.cybersource.com';
$settings['cybersource']['url']['prod'] = 'https://secureacceptance.cybersource.com';

$settings['cybersource']['accepted_cards'] = ['visa', 'mastercard'];
