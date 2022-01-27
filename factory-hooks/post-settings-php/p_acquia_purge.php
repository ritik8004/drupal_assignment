<?php

/**
 * @file
 * Implementation of ACSF post-settings-php hook to set unique queue service.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// All the non-prod sites are on this balancer pair.
// For production, we add them in the per brand settings file on server.
if ($settings['env_name'] !== 'live') {
  $settings['reverse_proxies'] = [
    'bal-6657.enterprise-g1.hosting.acquia.com',
    'bal-6658.enterprise-g1.hosting.acquia.com',
  ];
}
