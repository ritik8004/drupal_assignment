<?php

/**
 * @file
 * Example implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

require DRUPAL_ROOT . '/../vendor/acquia/blt/settings/blt.settings.php';

$env = 'local';
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $env = $_ENV['AH_SITE_ENVIRONMENT'];
}
elseif (getenv('TRAVIS')) {
  $env = 'travis';
}

// Set the env in settings to allow re-using in custom code.
$settings['env'] = $env;

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

// Common ACM settings.
$settings['store_id']['en'] = 1;
$settings['store_id']['ar'] = 3;

$settings['alshaya_api.settings']['magento_lang_prefix'] = 'kwt_';
$settings['alshaya_api.settings']['magento_api_base'] = 'rest/V1';
$settings['alshaya_api.settings']['verify_ssl'] = 0;

// TODO: Security.
$settings['alshaya_api.settings']['username'] = 'acquiaapi';
$settings['alshaya_api.settings']['password'] = 'password123';

// Security - autologout settings.
$settings['autologout.settings']['timeout'] = 1200;

// Set the debug dir of conductor.
$config['acq_commerce.conductor']['debug_dir'] = '/home/alshaya/' . $env;
$config['acq_commerce.conductor']['debug'] = TRUE;

// Set page size to sync products to 30.
$settings['acq_commerce.conductor']['product_page_size'] = 30;

// Default language code for redirects.
$settings['alshaya_i18n.settings']['default_langcode'] = 'ar';

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

switch ($env) {
  case 'local':
    // Specify the modules to be enabled on this env.
    $settings['additional_modules'][] = 'dblog';
    $settings['additional_modules'][] = 'views_ui';
    $settings['additional_modules'][] = 'features_ui';

    $config['autologout.settings']['timeout'] = 86400;

    $config['simple_oauth.settings']['private_key'] = $settings['alshaya_acm_soauth_private_key'];
    $config['simple_oauth.settings']['public_key'] = $settings['alshaya_acm_soauth_public_key'];

  case 'travis':
    // Disable stock check in local.
    global $_alshaya_acm_disable_stock_check;
    $_alshaya_acm_disable_stock_check = TRUE;

    $config['acq_commerce.conductor']['debug'] = FALSE;
    break;

  case '01dev':
  case '01dev2':
  case '01dev3':
  case '01test':
    // Specify the modules to be enabled on this env.
    $settings['additional_modules'][] = 'dblog';
    $settings['additional_modules'][] = 'views_ui';
    $settings['additional_modules'][] = 'purge_ui';
    break;

  default:
    // Don't debug by default on unknown ENV.
    $settings['acq_commerce.conductor']['debug'] = FALSE;
}
