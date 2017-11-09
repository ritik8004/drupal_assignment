<?php

/**
 * @file
 * Example implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

require DRUPAL_ROOT . '/../vendor/acquia/blt/settings/blt.settings.php';

// Identify which env we are acting on.
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

// Set the knet resource path which should be outside GIT root.
$knet_resource_dir = $env == 'local' ? '/home/vagrant/knet-resource/' : '/home/alshaya/knet-resource/' . $env . '/mckw/';
$settings['alshaya_acm_knet.settings']['resource_path'] = $knet_resource_dir;
$settings['alshaya_acm_knet.settings']['use_secure_response_url'] = 0;
// KWD is 414.
$settings['alshaya_acm_knet.settings']['knet_currency_code'] = '414';

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
$settings['acq_commerce.conductor']['debug'] = TRUE;

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

switch ($env) {
  case 'local':
    $config['acq_commerce.conductor']['debug_dir'] = '/home/vagrant/';

    // Specify the modules to be enabled on this env.
    $settings['additional_modules'][] = 'dblog';
    $settings['additional_modules'][] = 'views_ui';
    $settings['additional_modules'][] = 'features_ui';

    $settings['autologout.settings']['timeout'] = 86400;

  case 'travis':
    // Disable stock check in local.
    global $_alshaya_acm_disable_stock_check;
    $_alshaya_acm_disable_stock_check = TRUE;

    $settings['acq_commerce.conductor']['debug'] = FALSE;

    $settings['acq_commerce.conductor']['url'] = 'https://alshaya-dev.eu-west-1.prod.acm.acquia.io/';
    $settings['acq_commerce.conductor']['hmac_id'] = 'uAfqsl!BMf5xd8Z';
    $settings['acq_commerce.conductor']['hmac_secret'] = 'eS#8&0@XyegNUO';
    $settings['alshaya_api.settings']['magento_host'] = 'https://conductor-update-alqhiyq-z3gmkbwmwrl4g.eu.magentosite.cloud';
    break;

  case '01dev':
    $settings['acq_commerce.conductor']['url'] = 'https://alshaya-dev.eu-west-1.prod.acm.acquia.io/';
    $settings['acq_commerce.conductor']['hmac_id'] = 'uAfqsl!BMf5xd8Z';
    $settings['acq_commerce.conductor']['hmac_secret'] = 'eS#8&0@XyegNUO';
    $settings['alshaya_api.settings']['magento_host'] = 'https://conductor-update-alqhiyq-z3gmkbwmwrl4g.eu.magentosite.cloud';

    // Specify the modules to be enabled on this env.
    $settings['additional_modules'][] = 'dblog';
    $settings['additional_modules'][] = 'views_ui';
    $settings['additional_modules'][] = 'purge_ui';
    break;

  case '01test':
    $settings['acq_commerce.conductor']['url'] = 'https://alshaya-test.eu-west-1.prod.acm.acquia.io/';
    $settings['acq_commerce.conductor']['hmac_id'] = 'uAfqsl!BMf5xd8Z';
    $settings['acq_commerce.conductor']['hmac_secret'] = 'eS#8&0@XyegNUO';
    $settings['alshaya_api.settings']['magento_host'] = 'https://qa-h47ppbq-z3gmkbwmwrl4g.eu.magentosite.cloud';

    // Specify the modules to be enabled on this env.
    $settings['additional_modules'][] = 'dblog';
    $settings['additional_modules'][] = 'views_ui';
    $settings['additional_modules'][] = 'purge_ui';
    break;

  case '01uat':
    $settings['acq_commerce.conductor']['url'] = 'https://alshaya-uat.eu-west-1.prod.acm.acquia.io/';
    $settings['acq_commerce.conductor']['hmac_id'] = 'uAfqsl!BMf5xd8Z';
    $settings['acq_commerce.conductor']['hmac_secret'] = 'eS#8&0@XyegNUO';
    $settings['alshaya_api.settings']['magento_host'] = 'https://staging-api.mothercare.com.kw.c.z3gmkbwmwrl4g.ent.magento.cloud';
    break;

  case '01live':
  case '01update':
    // Don't debug by default on Prod ENV.
    $settings['acq_commerce.conductor']['debug'] = FALSE;

    $settings['acq_commerce.conductor']['url'] = 'https://alshaya-prod.eu-west-1.prod.acm.acquia.io/';
    $settings['acq_commerce.conductor']['hmac_id'] = 'uAfqsl!BMf5xd8Z';
    $settings['acq_commerce.conductor']['hmac_secret'] = 'eS#8&0@XyegNUO';
    $settings['alshaya_api.settings']['magento_host'] = 'http://mcmena.store.alshaya.com';

    // Disable setting readonly automatically on prod.
    $config['acquia_search.settings']['disable_auto_switch'] = TRUE;
    break;

  default:
    // Don't debug by default on unknown ENV.
    $settings['acq_commerce.conductor']['debug'] = FALSE;

    $settings['acq_commerce.conductor']['url'] = 'https://alshaya-dev.eu-west-1.prod.acm.acquia.io/';
    $settings['acq_commerce.conductor']['hmac_id'] = 'uAfqsl!BMf5xd8Z';
    $settings['acq_commerce.conductor']['hmac_secret'] = 'eS#8&0@XyegNUO';
    $settings['alshaya_api.settings']['magento_host'] = 'https://conductor-update-alqhiyq-z3gmkbwmwrl4g.eu.magentosite.cloud';
}

// Recaptcha settings.
$settings['recaptcha.settings']['site_key'] = '6Le93BsUAAAAAMOiJ5wrk4ICF0N-dLs6iM_eR4di';
$settings['recaptcha.settings']['secret_key'] = '6Le93BsUAAAAABQ0RMy0TIFuKasg3uz8hqVl4c6n';
