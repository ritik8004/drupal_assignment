<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$dir = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'apple-pay-resources' . DIRECTORY_SEPARATOR;
$settings['apple_pay_secret_info']['merchantCertificate'] = $dir . 'merchant_id.cer';
$settings['apple_pay_secret_info']['processingCertificate'] = $dir . 'apple_pay.cer';
$settings['apple_pay_secret_info']['processingCertificatePass'] = '';
