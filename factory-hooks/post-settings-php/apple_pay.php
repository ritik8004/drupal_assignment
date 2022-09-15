<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$dir = $settings['alshaya_home_dir'] . DIRECTORY_SEPARATOR . 'apple-pay-resources' . DIRECTORY_SEPARATOR;
$settings['apple_pay_secret_info']['merchantCertificateKey'] = $dir . 'merchant_id.key';
$settings['apple_pay_secret_info']['merchantCertificatePem'] = $dir . 'merchant_id.pem';
$settings['apple_pay_secret_info']['merchantCertificatePass'] = '';
