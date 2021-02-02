<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

require_once DRUPAL_ROOT . '/../factory-hooks/environments/environments.php';
$env = alshaya_get_site_environment();

// Set home directory as per environment.
($env === 'local') ? $home = '/home/vagrant' : $home = $_SERVER['HOME'];

$dir = $home . DIRECTORY_SEPARATOR . 'apple-pay-resources' . DIRECTORY_SEPARATOR;
$settings['apple_pay_secret_info']['merchantCertificateKey'] = $dir . 'merchant_id.key';
$settings['apple_pay_secret_info']['merchantCertificatePem'] = $dir . 'merchant_id.pem';
$settings['apple_pay_secret_info']['merchantCertificatePass'] = '';
