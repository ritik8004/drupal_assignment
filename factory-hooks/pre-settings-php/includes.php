<?php

/**
 * @file
 * Example implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

require DRUPAL_ROOT . '/../vendor/acquia/blt/settings/blt.settings.php';
include_once DRUPAL_ROOT . '/../factory-hooks/environments/includes.php';

$env = 'local';
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $env = $_ENV['AH_SITE_ENVIRONMENT'];
}
elseif (getenv('TRAVIS')) {
  $env = 'travis';
}

global $site_name;

// Set the env in settings to allow re-using in custom code.
$settings['env'] = $env;

switch ($env) {
  case 'local':
    $settings['social_auth_facebook.settings']['app_id'] = '2140208022890023';
    $settings['social_auth_facebook.settings']['app_secret'] = '7cde10657c1866f072c56283af920484';
    $settings['social_auth_facebook.settings']['graph_version'] = '3.0';
    break;

  case '01live':
    $file_path = '/home/alshaya/settings/' . $env . '/';
    $facebook_site_settings_file = $file_path . $site_name . '/facebook.php';
    $facebook_default_settings_file = $file_path . 'default/facebook.php';
    if (file_exists($facebook_site_settings_file)) {
      include_once $facebook_site_settings_file;
    }
    elseif (file_exists($facebook_default_settings_file)) {
      include_once $facebook_default_settings_file;
    }
    break;

  default:
    $settings['social_auth_facebook.settings']['app_id'] = '452346355260372';
    $settings['social_auth_facebook.settings']['app_secret'] = '466de9be713752a2f19eb566270013ab';
    $settings['social_auth_facebook.settings']['graph_version'] = '3.0';
    break;
}

// Configure your hash salt here.
// TODO: Security.
// $settings['hash_salt'] = '';

// Shield.
// TODO: Security.
$settings['alshaya_custom_shield_default_user'] = 'alshaya_shield';
$settings['alshaya_custom_shield_default_pass'] = 'AS_S';

// ACM user.
// TODO: Security.
$settings['alshaya_acm_user_username'] = 'alshaya_acm';
$settings['alshaya_acm_user_email'] = 'noreply-acm@alshaya.com';
$settings['alshaya_acm_user_password'] = 'AlShAyA_AcM';

$settings['alshaya_magento_user_username'] = 'alshaya_magento';
$settings['alshaya_magento_user_email'] = 'noreply-magento@alshaya.com';
$settings['alshaya_magento_user_password'] = 'AlShAyA_MaGeNtO';

// Simple Oauth.
// TODO: Security.
$soauth_key_dir = '';
$soauth_key_name = 'alshaya_acm';
if ($env == 'local') {
  $soauth_key_dir = '/var/www/alshaya/box/';
}
elseif ($env == 'travis') {
  $soauth_key_dir = '/home/travis/build/acquia-pso/alshaya/private/';
  $soauth_key_name = 'travis_acm';
}
else {
  $soauth_key_dir = '/home/alshaya/simple-oauth/' . $env . '/';
}
$settings['alshaya_acm_soauth_public_key'] = $soauth_key_dir . $soauth_key_name . '.pub';
$settings['alshaya_acm_soauth_private_key'] = $soauth_key_dir . $soauth_key_name;
$settings['alshaya_acm_soauth_client_secret'] = 'AlShAyA';
$settings['alshaya_acm_soauth_client_uuid'] = '35b9a28a-939f-4e2b-be55-9445c5b6549e';

$settings['alshaya_magento_soauth_client_uuid'] = '4cacd535-3b24-434e-9d32-d6e843f7b91a';
$settings['alshaya_magento_soauth_client_secret'] = 'AlShAyA';

$settings['alshaya_api.settings']['magento_api_base'] = 'rest/V1';
$settings['alshaya_api.settings']['verify_ssl'] = 0;

// Security - autologout settings.
$settings['autologout.settings']['timeout'] = 1200;

// Set the debug dir of conductor.
$config['acq_commerce.conductor']['debug_dir'] = '/home/alshaya/' . $env;
$config['acq_commerce.conductor']['debug'] = FALSE;

// Set page size to sync products to 30.
$settings['acq_commerce.conductor']['product_page_size'] = 30;

// Disable unwanted core views.
$settings['views_to_disable'] = [
  'frontpage',
  'profiles',
  'content_recent',
  'taxonomy_term',
  'who_s_new',
  'who_s_online',
];

// Specify the modules to be enabled/uninstalled - just initialised here.
$settings['additional_modules'] = [];

// Set page cache duration to 24 hours by default.
$config['system.performance']['cache']['page']['max_age'] = 14400;

// ################################################################
// This switch/case is ONLY for per environment settings. If any of these
// settings must be overridden on a per site basis, please, check
// factory-hooks/environments/settings.php to see the other settings.
// ################################################################
switch ($env) {
  case 'local':
    // Specific/development modules to be enabled on this env.
    $settings['additional_modules'][] = 'dblog';
    $settings['additional_modules'][] = 'views_ui';
    $settings['additional_modules'][] = 'features_ui';

    // Increase autologout timeout on local so we are not always logged out.
    $config['autologout.settings']['timeout'] = 86400;

    $config['simple_oauth.settings']['private_key'] = $settings['alshaya_acm_soauth_private_key'];
    $config['simple_oauth.settings']['public_key'] = $settings['alshaya_acm_soauth_public_key'];

    // Log debug messages too.
    $settings['alshaya_performance_log_mode'] = 'developer';

  // Please note there is no "break" at the end of "local" case so "travis"
  // settings are applied both on "local" and on "travis" environments.
  case 'travis':
    // Disable stock check.
    global $_alshaya_acm_disable_stock_check;
    $_alshaya_acm_disable_stock_check = TRUE;
    break;

  case '01dev':
  case '01dev2':
  case '01dev3':
  case '01test':
    // Specific/development modules to be enabled on this env.
    $settings['additional_modules'][] = 'dblog';
    $settings['additional_modules'][] = 'views_ui';
    $settings['additional_modules'][] = 'purge_ui';

    // Log debug messages too.
    $settings['alshaya_performance_log_mode'] = 'developer';

    // We only debug on ACSF dev/test environments.
    $config['acq_commerce.conductor']['debug'] = TRUE;
    break;
}
