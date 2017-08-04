<?php

/**
 * @file
 * Example implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Configure your hash salt here.
// $settings['hash_salt'] = '';

require DRUPAL_ROOT . '/../vendor/acquia/blt/settings/blt.settings.php';

// Travis case.
if (empty($config_directories)) {
  // Configuration directories.
  $dir = dirname(DRUPAL_ROOT);
  $config_directories['sync'] = $dir . "/config/$site_dir";
}

// Default credentials.
// TODO: Security.
$settings['alshaya_custom_shield_default_user'] = 'alshaya_shield';
$settings['alshaya_custom_shield_default_pass'] = 'AS_S';

// TODO: Security.
$settings['alshaya_acm_user_username'] = 'alshaya_acm';
$settings['alshaya_acm_user_email'] = 'noreply-acm@alshaya.com';
$settings['alshaya_acm_user_password'] = 'AlShAyA_AcM';

// Conductor settings.
$env = isset($_ENV['AH_SITE_ENVIRONMENT']) ? $_ENV['AH_SITE_ENVIRONMENT'] : 'local';

// Set the knet resource path which should be outside GIT root.
$config['alshaya_acm_knet.settings']['resource_path'] = '/home/alshaya/knet-resource/' . $env . '/mckw/';
$config['alshaya_acm_knet.settings']['use_secure_response_url'] = 0;

// TODO: Security.
$soauth_key_dir = $env == 'local' ? '/var/www/alshaya/box/' : '/home/alshaya/simple-oauth/' . $env . '/';
$settings['alshaya_acm_soauth_public_key'] = $soauth_key_dir . 'alshaya_acm.pub';
$settings['alshaya_acm_soauth_private_key'] = $soauth_key_dir . 'alshaya_acm';
$settings['alshaya_acm_soauth_client_secret'] = 'AlShAyA';
$settings['alshaya_acm_soauth_client_uuid'] = '35b9a28a-939f-4e2b-be55-9445c5b6549e';

// Set the debug dir of conductor.
$config['acq_commerce.conductor']['debug_dir'] = '/home/alshaya/' . $env;
$config['acq_commerce.conductor']['debug'] = TRUE;

switch ($env) {
  case 'local':
    // Disable stock check in local.
    global $_alshaya_acm_disable_stock_check;
    $_alshaya_acm_disable_stock_check = TRUE;

    // Set the knet resource path which should be outside GIT root.
    $config['alshaya_acm_knet.settings']['resource_path'] = '/home/vagrant/knet-resource/';
    $config['acq_commerce.conductor']['debug'] = FALSE;

  case '01dev':
  case '01test':
    $config['acq_commerce.conductor']['url'] = 'https://alshaya-dev.prod.acm.acquia.io/';
    $config['acq_commerce.conductor']['hmac_id'] = 'uAfqsl!BMf5xd8Z';
    $config['acq_commerce.conductor']['hmac_secret'] = 'eS#8&0@X$yegNUO';

    $config['alshaya_api.settings']['magento_host'] = 'https://master-7rqtwti-z3gmkbwmwrl4g.eu.magentosite.cloud';
    $config['alshaya_api.settings']['magento_lang_prefix'] = 'kwt_';
    $config['alshaya_api.settings']['magento_api_base'] = 'rest/V1';
    $config['alshaya_api.settings']['verify_ssl'] = 0;

    $config['alshaya_api.settings']['username'] = 'acquiaapi';
    $config['alshaya_api.settings']['password'] = 'gF2Fkndy8Erb';
  break;

  default:
    $config['acq_commerce.conductor']['url'] = 'https://pprod.dev.alshaya.acm.acquia.io/';

    $config['alshaya_api.settings']['magento_host'] = 'http://staging-api.mothercare.com.kw.c.z3gmkbwmwrl4g.ent.magento.cloud';
    $config['alshaya_api.settings']['magento_lang_prefix'] = 'kwt_';
    $config['alshaya_api.settings']['magento_api_base'] = 'rest/V1';
    $config['alshaya_api.settings']['verify_ssl'] = 0;

    $config['alshaya_api.settings']['username'] = 'acquiaapi';
    $config['alshaya_api.settings']['password'] = 'gF2Fkndy8Erb';
    break;
}

// Recaptcha settings.
$config['recaptcha.settings']['site_key'] = '6Le93BsUAAAAAMOiJ5wrk4ICF0N-dLs6iM_eR4di';
$config['recaptcha.settings']['secret_key'] = '6Le93BsUAAAAABQ0RMy0TIFuKasg3uz8hqVl4c6n';
