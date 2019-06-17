<?php
// @codingStandardsIgnoreFile

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
$settings['alshaya_knet.settings']['resource_path'] = $knet_resource_dir;

// We have valid SSL now, by default we will use secure response url.
$settings['alshaya_knet.settings']['use_secure_response_url'] = 1;

// KWD is 414.
$settings['alshaya_knet.settings']['knet_currency_code'] = '414';

// Knet udf5 prefix.
$settings['alshaya_knet.settings']['knet_udf5_prefix'] = 'ptlf';

// For the new K-Net toolkit.
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  // Storing K-Net information like id, password, key in secret information
  // file for new toolkit.
  // @see `secrets.settings.php` in `/mnt/files/`
  $secrets_file = sprintf('/mnt/files/%s.%s/secrets.settings.php', $_ENV['AH_SITE_GROUP'], $_ENV['AH_SITE_ENVIRONMENT']);
  if (file_exists($secrets_file)) {
    require $secrets_file;
  }
}
$settings['knet'][$acsf_site_name]['knet_url'] = 'https://kpaytest.com.kw/kpg/PaymentHTTP.htm';
// For prod env, K-Net redirecting url is different.
if ($env == '01live' || $env == '01update') {
  $settings['knet'][$acsf_site_name]['knet_url'] = 'https://kpay.com.kw/kpg/PaymentHTTP.htm';
}
