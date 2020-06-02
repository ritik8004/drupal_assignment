<?php
// @codingStandardsIgnoreFile

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
