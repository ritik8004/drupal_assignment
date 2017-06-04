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
$settings['alshaya_custom_shield_default_user'] = 'alshaya_shield';
$settings['alshaya_custom_shield_default_pass'] = 'AS_S';

// Conductor settings.
$env = isset($_ENV['AH_SITE_ENVIRONMENT']) ? $_ENV['AH_SITE_ENVIRONMENT'] : 'local';

// 01 is prefixed most of the time so we don't get proper env here.
// Clean the env, we do it only for dev and test.
if (strpos($env, 'dev') !== FALSE) {
  $env = 'dev';
}
elseif (strpos($env, 'test') !== FALSE) {
  $env = 'test';
}

switch ($env) {
  case 'local':
    global $_alshaya_acm_disable_stock_check;
    $_alshaya_acm_disable_stock_check = TRUE;
  case 'dev':
  case 'test':
    $config['alshaya_api.settings']['magento_host'] = 'https://master-7rqtwti-z3gmkbwmwrl4g.eu.magentosite.cloud';
    $config['alshaya_api.settings']['magento_api_base'] = 'rest/V1';
    $config['alshaya_api.settings']['consumer_key'] = 'o6hdclkp8553h6r86a7wri2289j7spoa';
    $config['alshaya_api.settings']['consumer_secret'] = 'eamlm6tpmpju9gwk2st6hbed1i5h184a';
    $config['alshaya_api.settings']['access_token'] = 'mffooaoteilvex7nh163b47l4hhm54pn';
    $config['alshaya_api.settings']['access_token_secret'] = 'h32bsfv7gnsjf9frbk2n06v67nrffgw1';
    $config['alshaya_api.settings']['verify_ssl'] = 0;

    $config['acq_commerce.conductor']['url_agent'] = 'https://agent.dev.acm.acquia.io/';
    $config['acq_commerce.conductor']['url_ingest'] = 'https://ingest.dev.acm.acquia.io/';
    break;

  default:
    $config['alshaya_api.settings']['magento_host'] = 'https://master-7rqtwti-z3gmkbwmwrl4g.eu.magentosite.cloud';
    $config['alshaya_api.settings']['magento_api_base'] = 'rest/V1';
    $config['alshaya_api.settings']['consumer_key'] = 'o6hdclkp8553h6r86a7wri2289j7spoa';
    $config['alshaya_api.settings']['consumer_secret'] = 'eamlm6tpmpju9gwk2st6hbed1i5h184a';
    $config['alshaya_api.settings']['access_token'] = 'mffooaoteilvex7nh163b47l4hhm54pn';
    $config['alshaya_api.settings']['access_token_secret'] = 'h32bsfv7gnsjf9frbk2n06v67nrffgw1';
    $config['alshaya_api.settings']['verify_ssl'] = FALSE;

    $config['acq_commerce.conductor']['url_agent'] = 'https://agent.dev.alshaya.acm.acquia.io/';
    $config['acq_commerce.conductor']['url_ingest'] = 'https://ingest.dev.alshaya.acm.acquia.io/';
    break;
}

$config['acq_commerce.conductor']['verify_ssl'] = FALSE;

// Recaptcha settings.
$config['recaptcha.settings']['site_key'] = '6Le93BsUAAAAAMOiJ5wrk4ICF0N-dLs6iM_eR4di';
$config['recaptcha.settings']['secret_key'] = '6Le93BsUAAAAABQ0RMy0TIFuKasg3uz8hqVl4c6n';
