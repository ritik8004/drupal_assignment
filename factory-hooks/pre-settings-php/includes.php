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

// Test Alshaya.
// $config['google_tag.settings']['container_id'] = 'GTM-PP5PK4C';

switch ($env) {
  case 'local':
    global $_alshaya_acm_disable_stock_check;
    $_alshaya_acm_disable_stock_check = TRUE;
  case 'dev':
  case 'test':
    $config['acq_commerce.conductor']['url'] = 'https://uat.dev.alshaya.acm.acquia.io/';
    $config['alshaya_api.settings']['magento_host'] = 'https://master-7rqtwti-z3gmkbwmwrl4g.eu.magentosite.cloud';
    $config['alshaya_api.settings']['magento_api_base'] = 'rest/V1';
    $config['alshaya_api.settings']['verify_ssl'] = 0;

    $config['alshaya_api.settings']['username'] = 'acquiaapi';
    $config['alshaya_api.settings']['password'] = 'gF2Fkndy8Erb';
    break;

  default:
    $config['acq_commerce.conductor']['url'] = 'https://uat.dev.alshaya.acm.acquia.io/';

    $config['alshaya_api.settings']['magento_host'] = 'https://master-7rqtwti-z3gmkbwmwrl4g.eu.magentosite.cloud';
    $config['alshaya_api.settings']['magento_api_base'] = 'rest/V1';
    $config['alshaya_api.settings']['verify_ssl'] = FALSE;

    $config['alshaya_api.settings']['username'] = 'acquiaapi';
    $config['alshaya_api.settings']['password'] = 'gF2Fkndy8Erb';
    break;
}

$config['acq_commerce.conductor']['verify_ssl'] = FALSE;

// Recaptcha settings.
$config['recaptcha.settings']['site_key'] = '6Le93BsUAAAAAMOiJ5wrk4ICF0N-dLs6iM_eR4di';
$config['recaptcha.settings']['secret_key'] = '6Le93BsUAAAAABQ0RMy0TIFuKasg3uz8hqVl4c6n';
