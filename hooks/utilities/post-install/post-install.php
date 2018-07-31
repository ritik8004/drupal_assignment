<?php
/**
 * @file
 * Factory Hook: post-install.
 *
 * This hook enables you to execute PHP after a new website is created
 * in your subscription. Unlike most API-based hooks, this hook does not
 * take arguments, but instead executes the PHP code it is provided.
 *
 * This is used so that an ACSF site install will match a local BLT site
 * install. After a local site install, the update functions are run.
 *
 */

// Detect the site name (mckw, hmsa, bbwae, ...) from the domain.
$site_name = preg_replace('/\d/', '',explode('.', $_SERVER['HTTP_HOST'])[0]);

// Detect the brand name (mc, hm) and country code (kw, sa, ae) from the site name.
$site_code = substr($site_name, 0, -2);
$country_code = substr($site_name, -2);

echo $site_name . ' - ' . $site_code . ' - ' . $country_code;

if ($site_code !== 'vs') {
  exec(dirname(__FILE__) . 'post-install.sh "' . $_ENV['AH_SITE_ENVIRONMENT'] . '" "' . $site_code . '" "' . $country_code . '"');
}