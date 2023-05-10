<?php

/**
 * @file
 * Implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

if (getenv('LANDO') || getenv('IS_DDEV_PROJECT')) {
  // Keys for Google Auth in LANDO env.
  $config['social_auth_google.settings']['client_id'] = '847720644464-1bqotkjgsovehdtna2d63m706slkjaba.apps.googleusercontent.com';
  $config['social_auth_google.settings']['client_secret'] = 'ERdHm6oEVXWdf_Xwp1-de_rf';

  // Keys for Facebook Auth in LANDO env.
  // This will work only on specific domains, to investigate any issue please
  // use them. Please check readme to know the list.
  $config['social_auth_facebook.settings']['app_id'] = '892945587781716';
  $config['social_auth_facebook.settings']['app_secret'] = '789ddb510b2b210c2f1a4010d2c77dd8';
}
