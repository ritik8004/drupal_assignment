<?php
// phpcs:ignoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * Set xhprof settings in local.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

if ($settings['env'] === 'local') {
  // Default settings.
  $config['xhprof.config']['extension'] = 'tideways_xhprof';
  $config['xhprof.config']['flags']['FLAGS_CPU'] = '1';
  $config['xhprof.config']['flags']['FLAGS_MEMORY'] = '1';

  // Disable xhprof by default.
  $config['xhprof.config']['enabled'] = 0;

  if (strpos($_SERVER['REQUEST_URI'] ?? '', 'xhprof') > -1) {
    // Below code is added to fix space and special character issues in xhprof.
    global $host_site_code;
    $config['system.site']['name'] = $host_site_code;
  }

  // Enable only if we have profile parameter set.
  if (isset($_REQUEST['profile'])) {
    // Below code is added to fix space and special character issues in xhprof.
    global $host_site_code;
    $config['system.site']['name'] = $host_site_code;

    $config['xhprof.config']['enabled'] = 1;
  }
}
