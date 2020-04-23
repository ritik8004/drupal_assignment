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
