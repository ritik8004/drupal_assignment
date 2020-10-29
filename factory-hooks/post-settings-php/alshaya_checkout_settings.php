<?php

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$settings['alshaya_checkout_settings']['place_order_double_check_after_exception'] = TRUE;
$settings['alshaya_checkout_settings']['cancel_reservation_enabled'] = FALSE;

// Allow all validations to be finished for place order.
$settings['alshaya_checkout_settings']['place_order_timeout'] = 60;

// Check all payments initiated before 500 seconds.
$settings['alshaya_checkout_settings']['pending_payments']['before'] = 500;

// Do not check payments initiated before 4 hours.
$settings['alshaya_checkout_settings']['pending_payments']['after'] = 14400;

// Add purchase expiration time in minutes.
$settings['alshaya_checkout_settings']['purchase_expiration_time'] = 15;
