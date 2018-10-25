<?php
/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$env = 'local';

if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $env = $_ENV['AH_SITE_ENVIRONMENT'];
}
elseif (getenv('TRAVIS')) {
  $env = 'travis';
}

global $acsf_site_name;

// Set the knet resource path which should be outside GIT root.
$knet_resource_dir = $env == 'local' ? '/home/vagrant/knet-resource/' : '/home/alshaya/knet-resource/' . $env . '/' . $acsf_site_name . '/';
$settings['alshaya_acm_knet.settings']['resource_path'] = $knet_resource_dir;

// We have valid SSL now, by default we will use secure response url.
$settings['alshaya_acm_knet.settings']['use_secure_response_url'] = 1;

// KWD is 414.
$settings['alshaya_acm_knet.settings']['knet_currency_code'] = '414';

// Knet udf5 prefix.
$settings['alshaya_acm_knet.settings']['knet_udf5_prefix'] = 'ptlf';
