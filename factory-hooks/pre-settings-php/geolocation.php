<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Set google map api key.
$settings['geolocation_google_maps.settings']['google_map_api_key'] = 'AIzaSyCfNUqK2pIA8TTJusCyLGiypSbnxBe8fJ8';

// Set the key to use in Lando env directly in $config.
if (getenv('LANDO') || getenv('IS_DDEV_PROJECT')) {
  $config['geolocation_google_maps.settings']['google_map_api_key'] = 'AIzaSyBL9faHw5s_vO1sUalcbQv05dzce_71fUY';
}
