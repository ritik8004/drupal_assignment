<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Disable sitelink searchbox for non-prod env.
$settings['alshaya_seo.google_sitelink_searchbox_enable'] = 0;

// Enable sitelink searchbox for prod env.
if (isset($_ENV['AH_SITE_ENVIRONMENT']) && preg_match('/\d{2}(live|update)/', $_ENV['AH_SITE_ENVIRONMENT'])) {
  $settings['alshaya_seo.google_sitelink_searchbox_enable'] = 1;
}
