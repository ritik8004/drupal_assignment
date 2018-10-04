<?php
/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$env = 'local';
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $env = $_ENV['AH_SITE_ENVIRONMENT'];
}
elseif (getenv('TRAVIS')) {
  $env = 'travis';
}

switch ($env) {
  case 'local':
    $settings['social_auth_facebook.settings']['app_id'] = '2140208022890023';
    $settings['social_auth_facebook.settings']['app_secret'] = '7cde10657c1866f072c56283af920484';
    $settings['social_auth_facebook.settings']['graph_version'] = '3.0';
    break;

  case 'test':
  case 'uat':
  case 'dev':
  default:
    $settings['social_auth_facebook.settings']['app_id'] = '452346355260372';
    $settings['social_auth_facebook.settings']['app_secret'] = '466de9be713752a2f19eb566270013ab';
    $settings['social_auth_facebook.settings']['graph_version'] = '3.0';
    break;
}
