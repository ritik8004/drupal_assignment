<?php
/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

global $site_name;

// Get site name initials like 'mc' or 'hm'.
$site_name_initials = substr($site_name, 0, 2);

if ($site_name_initials == 'mc') {
  // MC google tag container id.
  $settings['google_tag.settings']['container_id'] = 'GTM-PP5PK4C';
}
elseif ($site_name_initials == 'hm') {
  // HnM google tag container id.
  $settings['google_tag.settings']['container_id'] = 'GTM-NQ4JXJP';
}
else {
  // For all other sites.
  $settings['google_tag.settings']['container_id'] = '';
}
