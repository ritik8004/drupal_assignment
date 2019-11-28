<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Example implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Configure your hash salt here.
// $settings['hash_salt'] = '';.
require DRUPAL_ROOT . '/../vendor/acquia/blt/settings/blt.settings.php';

$env = 'local';
$env_name = 'local';
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $env = $_ENV['AH_SITE_ENVIRONMENT'];
  $env_name = substr($env, 2);
}
elseif (getenv('TRAVIS')) {
  $env = 'travis';
  $env_name = 'travis';
}

// Set the env in settings to allow re-using in custom code.
$settings['env'] = $env;
$settings['env_name'] = $env_name;

// Set server home directory.
$settings['server_home_dir'] = ($env === 'local') ? '/home/vagrant' : $_SERVER['HOME'];

if ($settings['env'] === 'local') {
  // For Drush and other CLI commands increase the memory limit to 512 MB.
  // We do this only for local env, for cloud envs it is already done.
  // This is as suggested in https://support.acquia.com/hc/en-us/articles/360004542293-Conditionally-increasing-memory-limits
  $memory_limit = PHP_SAPI === 'cli' ? '512M' : '128M';
  ini_set('memory_limit', $memory_limit);

  global $host_site_code;

  // Get site code from site uri.
  if (!empty($_SERVER['HTTP_HOST'])) {
    $hostname_parts = explode('.', $_SERVER['HTTP_HOST']);
    $host_site_code = str_replace('alshaya-', '', $hostname_parts[1]);
  }
  else {
    foreach ($_SERVER['argv'] as $arg) {
      preg_match('/[\\S|\\s|\\d|\\D]*local.alshaya-(\\S*).com/', $arg, $matches);
      if (!empty($matches)) {
        $host_site_code = $matches[1];
        break;
      }
    }
  }

  // Set private files directory for local, it is not set in
  // '/../vendor/acquia/blt/settings/filesystem.settings.php' file.
  $settings['file_private_path'] = '/var/www/alshaya/files-private/' . $host_site_code;

  // Set config of stage file proxy to ignore invalid ssl errors.
  $config['stage_file_proxy.settings']['verify'] = FALSE;
}

// Social app keys for register/login.
$social_config = [
  'local' => [
    'facebook' => [
      'app_id' => '383866358862411',
      'app_secret' => '0646a2901853b99062ff2d15127db379',
    ],
  ],
  'test' => [
    'facebook' => [
      'app_id' => '452346355260372',
      'app_secret' => '466de9be713752a2f19eb566270013ab',
    ],
  ],
  'dev' => [
    'facebook' => [
      'app_id' => '2104286043129625',
      'app_secret' => '8af1b7ca4f9d21fd02ff626ee8a2a004',
    ],
  ],
  'pprod' => [
    'facebook' => [
      'app_id' => '844066215928270',
      'app_secret' => '107a72f9b68c6a62baaf917adb1fc9d6',
    ],
  ],
  'qa2' => [
    'facebook' => [
      'app_id' => '2376200055744873',
      'app_secret' => '1dd9679fcc9ba1ba3bdacb87da115a14',
    ],
  ],
  'dev2' => [
    'facebook' => [
      'app_id' => '615516092244231',
      'app_secret' => 'f0eaa4fd253010c23efb9cc3802ca5fd',
    ],
  ],
  'dev3' => [
    'facebook' => [
      'app_id' => '357400338223237',
      'app_secret' => '66354c2dc14b3dbbd9024425148d52b9',
    ],
  ],
  'uat' => [
    'facebook' => [
      'app_id' => '307987113196828',
      'app_secret' => '019eda6862dd77160f64a681113dfb0f',
    ],
  ],
];

if (!empty($social_config[$env_name])) {
  foreach ($social_config[$env_name] as $provider => $provider_config) {
    $settings["social_auth_{$provider}.settings"] = $provider_config;
  }
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

$settings['alshaya_mobile_app_user_username'] = 'alshaya_mobile_app';
$settings['alshaya_mobile_app_user_email'] = 'noreply-mobile-app@alshaya.com';
$settings['alshaya_mobile_app_user_password'] = 'AlShAyA_MoBiLe';

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
  $soauth_key_dir = $settings['server_home_dir'] . '/simple-oauth/' . $env . '/';
}
$settings['alshaya_acm_soauth_public_key'] = $soauth_key_dir . $soauth_key_name . '.pub';
$settings['alshaya_acm_soauth_private_key'] = $soauth_key_dir . $soauth_key_name;
$settings['alshaya_acm_soauth_client_secret'] = 'AlShAyA';
$settings['alshaya_acm_soauth_client_uuid'] = '35b9a28a-939f-4e2b-be55-9445c5b6549e';

$settings['alshaya_magento_soauth_client_uuid'] = '4cacd535-3b24-434e-9d32-d6e843f7b91a';
$settings['alshaya_magento_soauth_client_secret'] = 'AlShAyA';

$settings['alshaya_mobile_app_soauth_client_uuid'] = 'ac73dcc7-6918-4e14-8b48-86b5cd17f4d2';
$settings['alshaya_mobile_app_soauth_client_secret'] = 'AlShAyA';

$settings['alshaya_api.settings']['magento_api_base'] = 'rest/V1';
$settings['alshaya_api.settings']['verify_ssl'] = 0;

// Security - autologout settings.
$settings['autologout.settings']['timeout'] = 1200;

// Set the debug dir of conductor.
$config['acq_commerce.conductor']['debug_dir'] = $settings['server_home_dir'] . DIRECTORY_SEPARATOR . $env;
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

// Set page cache duration to 4hr by default.
$config['system.performance']['cache']['page']['max_age'] = 14400;

// ################################################################
// This switch/case is ONLY for per environment settings. If any of these
// settings must be overridden on a per site basis, please, check
// factory-hooks/environments/settings.php to see the other settings.
// ################################################################
switch ($env_name) {
  case 'local':
  case 'travis':
    // Requests from local are slow, we can to wait for some more time
    // while loading linked skus.
    $settings['linked_skus_timeout'] = 5;

    // Specific/development modules to be enabled on this env.
    $settings['additional_modules'][] = 'dblog';
    $settings['additional_modules'][] = 'views_ui';

    // Increase autologout timeout on local so we are not always logged out.
    $config['autologout.settings']['timeout'] = 86400;

    $config['simple_oauth.settings']['private_key'] = $settings['alshaya_acm_soauth_private_key'];
    $config['simple_oauth.settings']['public_key'] = $settings['alshaya_acm_soauth_public_key'];

    // Log debug messages too.
    $settings['alshaya_performance_log_mode'] = 'developer';
    break;

  case 'dev':
  case 'dev2':
  case 'dev3':
  case 'test':
    // Specific/development modules to be enabled on this env.
    $settings['additional_modules'][] = 'dblog';
    $settings['additional_modules'][] = 'views_ui';
    $settings['additional_modules'][] = 'purge_ui';

    // Log debug messages too.
    $settings['alshaya_performance_log_mode'] = 'developer';

    // We only debug on ACSF dev/test environments.
    $config['acq_commerce.conductor']['debug'] = TRUE;
    break;

  case 'live':
    // We want to timeout linked skus API call in 1 second on prod.
    $settings['linked_skus_timeout'] = 1;
    break;
}
