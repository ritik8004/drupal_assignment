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

// @TODO: Add a way to by-pass commerce data sync via post-install-override.txt.

define('ACTION_DISABLE', 'disable');
define('ACTION_OVERRIDE', 'override');

// Detect the site name (mckw, hmsa, bbwae, ...) from the domain.
$domain = preg_replace('/\d/', '', explode('.', $_SERVER['HTTP_HOST'])[0]);

// Detect the brand name (mc, hm) and country code (kw, sa, ae) from the site name.
$site_code = substr($domain, 0, -2);
$country_code = substr($domain, -2);

/**
 * Check if we need to override the arguments or cancel the process by reading
 * /home/alshaya/post-install-override.txt file. Expected content is:
 *
 * action: disable|override
 * brand_code: mc|hm|...
 * country_code: kw|sa|ae
 *
 * If the file exists and no action is configured, "disable" will be
 * considered the default action.
 */
if (file_exists('/home/alshaya/post-install-override.txt')) {
  $action = ACTION_DISABLE;

  $fh = fopen('/home/alshaya/post-install-override.txt','r');
  while ($line = fgets($fh)) {
    list($key, $value) = explode(': ', $line);
    switch ($key) {
      case 'action':
        $action = ($value == ACTION_DISABLE) ? ACTION_DISABLE : ACTION_OVERRIDE;
        break;

      case 'brand_code':
        $site_code = $value;
        break;

      case 'country_code':
        $country_code = $value;
        break;
    }
  }
  fclose($fh);

  // If post-install is disabled, stop here.
  if ($action == ACTION_DISABLE) {
    return;
  }
}

if ($site_code == 'vb') {
  $site_code = 'mc';
  exec(dirname(__FILE__) . '/../../scripts/setup/setup-fresh-site.sh "' . $_ENV['AH_SITE_ENVIRONMENT'] . '" "' . $domain . '" "' . $site_code . '" "' . $country_code . '"');
}
