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

// @TODO: Remove all the conductor + magento hosts settings.
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

    $settings['acq_commerce.conductor']['url'] = 'https://alshaya-hm-dev.eu-west-1.prod.acm.acquia.io/';
    $settings['acq_commerce.conductor']['hmac_id'] = '139fbcb466984b39aea5fd200984a2af';
    $settings['acq_commerce.conductor']['hmac_secret'] = 'oMSt6AXgn3TqlMVj5D8A3Q';
    $settings['alshaya_api.settings']['magento_host'] = 'https://qa-dc3i3ua-zbrr3sobrsb3o.eu.magentosite.cloud';
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
    $settings['alshaya_api.settings']['magento_host'] = 'https://master-7rqtwti-z3gmkbwmwrl4g.eu.magentosite.cloud';

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

  // R2 environments. While Conductor does not handle multiple Magento, all R2
  // environments are connected to the same Conductor and Magento.
  case '01dev2':
  case '01dev3':
    $settings['acq_commerce.conductor']['url'] = 'https://alshaya-hm-dev.eu-west-1.prod.acm.acquia.io/';
    $settings['acq_commerce.conductor']['hmac_id'] = '139fbcb466984b39aea5fd200984a2af';
    $settings['acq_commerce.conductor']['hmac_secret'] = 'oMSt6AXgn3TqlMVj5D8A3Q';
    $settings['alshaya_api.settings']['magento_host'] = 'https://qa-dc3i3ua-zbrr3sobrsb3o.eu.magentosite.cloud';
    break;

  case '01qa2':
    $settings['acq_commerce.conductor']['url'] = 'https://alshaya-hm-test.eu-west-1.prod.acm.acquia.io/';
    $settings['acq_commerce.conductor']['hmac_id'] = '0c1158d278c24ff586792de9bc01eaa7';
    $settings['acq_commerce.conductor']['hmac_secret'] = 'NMGIo_W2s4VA66_6UKM2pQ';
    $settings['alshaya_api.settings']['magento_host'] = 'https://qa-dc3i3ua-zbrr3sobrsb3o.eu.magentosite.cloud';
    break;

  default:
    $settings['acq_commerce.conductor']['url'] = 'https://alshaya-pprod.eu-west-1.prod.acm.acquia.io/';
    $settings['acq_commerce.conductor']['hmac_id'] = '75fba487a3043b35740e93a97513c3cf';
    $settings['acq_commerce.conductor']['hmac_secret'] = 'cVA/OSE1I1Zka3JZRFE';
    $settings['alshaya_api.settings']['magento_host'] = 'https://uat-irjkrqa-zbrr3sobrsb3o.eu.magentosite.cloud';
}
