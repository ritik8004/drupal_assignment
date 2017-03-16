<?php

/**
 * @file
 * Example implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Configure your hash salt here.
// $settings['hash_salt'] = '';

require DRUPAL_ROOT . '/../vendor/acquia/blt/settings/blt.settings.php';

$settings['alshaya_custom_shield_default_user'] = 'alshaya_shield';
$settings['alshaya_custom_shield_default_pass'] = 'AS_S';
