<?php
/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Set cybersource env to test by default.
$settings['acq_cybersource.settings']['env'] = 'test';