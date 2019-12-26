<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */
global $_acsf_site_name;

// Set the knet resource path which should be outside GIT root.
$knet_resource_dir = $settings['server_home_dir'] . '/knet-resource/';
if ($settings['env'] !== 'local') {
  $knet_resource_dir .= $settings['env'] . DIRECTORY_SEPARATOR . $_acsf_site_name . DIRECTORY_SEPARATOR;
}
$settings['alshaya_knet.settings']['resource_path'] = $knet_resource_dir;

// We have valid SSL now, by default we will use secure response url.
$settings['alshaya_knet.settings']['use_secure_response_url'] = 1;

// KWD is 414.
$settings['alshaya_knet.settings']['knet_currency_code'] = '414';

// Knet udf5 prefix.
$settings['alshaya_knet.settings']['knet_udf5_prefix'] = 'ptlf';

$settings['alshaya_knet.settings']['knet_url'] = 'https://kpaytest.com.kw/kpg/PaymentHTTP.htm';

if (preg_match('/\d{2}(live|update)/', $settings['env'])) {
  $settings['alshaya_knet.settings']['knet_url'] = 'https://kpay.com.kw/kpg/PaymentHTTP.htm';
}
