<?php

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Set cybersource env to test on all non-prod envs.
$settings['acq_cybersource.settings']['env'] = 'test';

if (preg_match('/\d{2}(live|update)/', $settings['env'])) {
  $settings['acq_cybersource.settings']['env'] = 'prod';
}
