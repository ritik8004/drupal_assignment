<?php

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$settings['alshaya_checkout_settings']['place_order_double_check_after_exception'] = TRUE;
$settings['alshaya_checkout_settings']['cancel_reservation_enabled'] = FALSE;

// Check all payments initiated before 500 seconds.
$settings['alshaya_checkout_settings']['pending_payments']['before'] = 500;

// Do not check payments initiated before 4 hours.
$settings['alshaya_checkout_settings']['pending_payments']['after'] = 14400;

// Revalidate total after 5 minutes if purchase not complete.
$settings['alshaya_checkout_settings']['totals_revalidation_ttl'] = 300;

// Flag to specify if we should use Native Magento API or ACM API.
$settings['alshaya_checkout_settings']['cart_operations_mode'] = 'native';

// Maximum attempts for the native mdc api.
$settings['alshaya_checkout_settings']['max_native_update_attempts'] = 1;

// Specify the mode to use to validate the cart.
// Modes - full cart update (full) or simple refresh action (refresh).
$settings['alshaya_checkout_settings']['cart_refresh_mode'] = 'refresh';
