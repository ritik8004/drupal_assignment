<?php

/**
 * @file
 * ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 *
 * phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis
 */

require_once DRUPAL_ROOT . '/../factory-hooks/environments/environments.php';

// Set the env in settings to allow re-using in custom code.
$env = alshaya_get_site_environment();
$env_name = !in_array($env, ['travis', 'local']) ? substr($env, 2) : $env;

$settings['env'] = $env;

// Set home directory as per environment.
$settings['alshaya_home_dir'] = $_SERVER['HOME'] ?? '';

if ($settings['env'] === 'local') {
  // Still keeping this condition for anyone using vagrant.
  $settings['alshaya_home_dir'] = '/home/vagrant';

  // For Lando we use custom directory for home.
  if (getenv('LANDO')) {
    $settings['alshaya_home_dir'] = '/app/local_home';
  }
  elseif (getenv('IS_DDEV_PROJECT')) {
    $settings['alshaya_home_dir'] = '/var/www/html/local_home';
  }
}

// Make sure environment name used to load settings is not pointing to update
// environment name like 01devup or 01update.
if ($env_name === 'update') {
  $env_name = 'live';
}
elseif (str_ends_with($env_name, 'up')) {
  $env_name = substr($env_name, 0, -2);
}

$settings['env_name'] = $env_name;

// Set server home directory.
$settings['server_home_dir'] = ($env === 'local') ? '/home/vagrant' : $_SERVER['HOME'];

if ($settings['env'] === 'local') {
  // For Drush and other CLI commands increase the memory limit to 512 MB.
  // We do this only for local env, for cloud envs it is already done.
  // This is as suggested in https://support.acquia.com/hc/en-us/articles/360004542293-Conditionally-increasing-memory-limits
  $memory_limit = PHP_SAPI === 'cli' ? '512M' : '128M';
  ini_set('memory_limit', $memory_limit);

  // phpcs:ignore
  global $host_site_code;

  if (!isset($host_site_code)) {
    // Retrieve host_site_code.
    require_once DRUPAL_ROOT . '/../factory-hooks/pre-settings-php/local_sites.php';
  }

  // Set private files directory for local, it is not set in
  // '/../vendor/acquia/blt/settings/filesystem.settings.php' file.
  $settings['file_private_path'] = DRUPAL_ROOT . '/../files-private/' . $host_site_code;

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
// @todo Security.
// $settings['hash_salt'] = '';.
// Shield.
// @todo Security.
$settings['alshaya_custom_shield_default_user'] = 'alshaya_shield';

// ACM user.
// @todo Security.
$settings['alshaya_acm_user_username'] = 'alshaya_acm';
$settings['alshaya_acm_user_email'] = 'noreply-acm@alshaya.com';

$settings['alshaya_magento_user_username'] = 'alshaya_magento';
$settings['alshaya_magento_user_email'] = 'noreply-magento@alshaya.com';

$settings['alshaya_mobile_app_user_username'] = 'alshaya_mobile_app';
$settings['alshaya_mobile_app_user_email'] = 'noreply-mobile-app@alshaya.com';

// Simple Oauth.
// @todo Security.
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

if ($env == 'local' || $env == 'travis') {
  // Set default value for local and travis enviornment.
  // secret settings file. See `post-settings/zzz_overrides`.
  $settings['alshaya_custom_shield_default_pass'] = 'travis';
  $settings['alshaya_acm_user_password'] = 'travis';
  $settings['alshaya_magento_user_password'] = 'travis';
  $settings['alshaya_mobile_app_user_password'] = 'travis';
  $settings['alshaya_acm_soauth_client_secret'] = 'travis';
  $settings['alshaya_acm_soauth_client_uuid'] = '8df19835-6c9d-4f36-b61e-1eb99cbee8de';
  $settings['alshaya_magento_soauth_client_uuid'] = 'b5e69c99-60a0-4ad4-a991-04d036f0d72f';
  $settings['alshaya_magento_soauth_client_secret'] = 'travis';
  $settings['alshaya_mobile_app_soauth_client_uuid'] = 'f2fc9587-9308-4801-87d9-e67767d4ae50';
  $settings['alshaya_mobile_app_soauth_client_secret'] = 'travis';
}

$settings['alshaya_acm_soauth_public_key'] = $soauth_key_dir . $soauth_key_name . '.pub';
$settings['alshaya_acm_soauth_private_key'] = $soauth_key_dir . $soauth_key_name;

$settings['alshaya_api.settings']['magento_api_base'] = 'rest/V1';
$settings['alshaya_api.settings']['verify_ssl'] = 0;

// Security - autologout settings.
$settings['autologout.settings']['timeout'] = 1200;

// Set the debug dir of conductor.
$config['acq_commerce.conductor']['debug_dir'] = $settings['server_home_dir'] . DIRECTORY_SEPARATOR . $env;
$config['acq_commerce.conductor']['debug'] = FALSE;

// Set page size to sync products to 30.
$settings['acq_commerce.conductor']['product_page_size'] = 30;

// Settings to serve empty response to bad bots.
// @see Drupal/alshaya_facets_pretty_paths/EventSubscriber/AlshayaFacetsPrettyPathsKernelEventsSubscriber
$settings['nonindexable_plp_filter_count'] = 2;
$settings['serve_empty_response_for_nonindexable_plp_to_bots'] = FALSE;
$settings['bad_bot_user_agents'] = [
  'googlebot',
  'petalbot',
  'yandexbot',
  'aspiegelbot',
  'bingbot',
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
    // Use PROXY for backend calls in local.
    $settings['alshaya_use_proxy'] = TRUE;

    // Requests from local are slow, we can to wait for some more time
    // while loading linked skus.
    $settings['linked_skus_timeout'] = 5;

    // Specific/development modules to be enabled on this env.
    $settings['additional_modules'][] = 'views_ui';

    // Increase autologout timeout on local so we are not always logged out.
    $config['autologout.settings']['timeout'] = 86400;

    $config['simple_oauth.settings']['private_key'] = $settings['alshaya_acm_soauth_private_key'];
    $config['simple_oauth.settings']['public_key'] = $settings['alshaya_acm_soauth_public_key'];

    // Log debug messages too.
    $settings['alshaya_performance_log_mode'] = 'developer';

    // Set this to 1 to make testing convenient.
    $config['alshaya_acm_product.settings']['local_storage_cache_time'] = 1;
    break;

  case 'dev':
  case 'dev2':
  case 'dev3':
  case 'test':
  case 'qa2':
    // Use PROXY for backend calls in lower non-prod environments.
    $settings['alshaya_use_proxy'] = TRUE;

    // Specific/development modules to be enabled on this env.
    $settings['additional_modules'][] = 'views_ui';
    $settings['additional_modules'][] = 'purge_ui';

    // Log debug messages too.
    $settings['alshaya_performance_log_mode'] = 'developer';

    // We only debug on ACSF dev/test environments.
    $config['acq_commerce.conductor']['debug'] = TRUE;

    // Set this to 1 to make testing convenient.
    $config['alshaya_acm_product.settings']['local_storage_cache_time'] = 1;
    break;

  case 'uat':
    break;

  case 'pprod':

    $settings['alshaya_use_proxy'] = TRUE;
    break;

  case 'live':
    // We want to timeout linked skus API call in 1 second on prod.
    $settings['linked_skus_timeout'] = 1;
    $settings['alshaya_drush_authenticate'] = TRUE;
    break;

}

// Disable importing translations from remote.
$config['locale.settings']['translation']['use_source'] = 'local';
