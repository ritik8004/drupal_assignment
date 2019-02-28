<?php
/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Set cybersource env to test on all non-prod envs.
$settings['acq_cybersource.settings']['env'] = 'test';

if (in_array($settings['env'], ['01live', '01update'])) {
  $settings['acq_cybersource.settings']['env'] = 'prod';
}
