<?php

/**
 * @file
 * ACSF post-sites-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Get the site's name from the first domain.
global $_acsf_site_name;
global $_acsf_site_name_full;

$_acsf_site_name_full = $_acsf_site_name;

// Support cases like hmkw1 or mckw12.
$_acsf_site_name = preg_replace('/\d/', '', $_acsf_site_name);
