<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Set higher number of items to be processed for purge via drush.
$settings['acquia_purge_ideal_conditions_limit_cli'] = 1000;
