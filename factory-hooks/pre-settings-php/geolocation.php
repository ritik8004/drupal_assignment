<?php
/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// If not prod env.
if (!in_array($settings['env'], ['01live', '01update'])) {
  // Set google map api key.
  $settings['geolocation.settings']['google_map_api_key'] = 'AIzaSyCx3OwGGlm7KqnrbZQIQ3FQLJgWKb3p5LI';
}
