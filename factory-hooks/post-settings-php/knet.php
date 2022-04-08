<?php
// phpcs:ignoreFile

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Get site environment.
require_once DRUPAL_ROOT . '/../factory-hooks/environments/environments.php';
$env = alshaya_get_site_environment();

global $_acsf_site_name;

// Set the knet resource path which should be outside GIT root.
$knet_resource_dir = $env == 'local' ? '/home/vagrant/knet-resource/' : '/home/alshaya/knet-resource/' . $env . '/' . $_acsf_site_name . '/';
$settings['alshaya_knet.settings']['resource_path'] = $knet_resource_dir;

// We have valid SSL now, by default we will use secure response url.
$settings['alshaya_knet.settings']['use_secure_response_url'] = 1;

// KWD is 414.
$settings['alshaya_knet.settings']['knet_currency_code'] = '414';

// Knet udf5 prefix.
$settings['alshaya_knet.settings']['knet_udf5_prefix'] = 'ptlf';

$settings['alshaya_knet.settings']['knet_base_url'] = 'https://kpaytest.com.kw';
if (preg_match('/\d{2}(live|update)/', $env)) {
  $settings['alshaya_knet.settings']['knet_base_url'] = 'https://kpay.com.kw';
}

// Keeping KNET payment URL in the main variable to avoid changes in code.
$settings['alshaya_knet.settings']['knet_url'] = $settings['alshaya_knet.settings']['knet_base_url'] . '/kpg/PaymentHTTP.htm';
