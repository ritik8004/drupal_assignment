<?php
// phpcs:ignoreFile

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

global $acsf_site_code;

/**
 * Brand file path.
 */
$settings['alshaya_brand_shared_folder'] = 'sites/g/files/' . $acsf_site_code;

$settings['media_download_timeout_video'] = 200;

// s3fs module will remove the config field for access_key and secret_key
// from '8.x-3.0-beta1' and read from settings so details are added here.
$settings['s3fs.access_key'] = 'AKIARGKNHDDL5AKRBP7S';
$settings['s3fs.secret_key'] = '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';

require_once DRUPAL_ROOT . '/../factory-hooks/environments/environments.php';

$env = alshaya_get_site_environment();
// This is to remove `01/02` etc from env name.
if (substr($env, 0, 1) == '0') {
  $env = substr($env, 2);
}

$bucket = match ($env) {
    'uat' => 'als-ecom-drupal-stopgap-uat-s3',
    'dev', 'dev2', 'dev3', 'qa2', 'test', 'local' => 'als-ecom-drupal-stopgap-dev-s3',
    default => '',
};

// Setting s3fs bucket and root folder.
$settings['s3fs.settings']['bucket'] = $bucket;
$settings['s3fs.settings']['root_folder'] = $env . '/' . $acsf_site_code . '/shared';
$settings['s3fs.settings']['region'] = 'eu-west-1';
