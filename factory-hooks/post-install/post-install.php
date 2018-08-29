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
$domain = preg_replace('/\d/', '',explode('.', $_SERVER['HTTP_HOST'])[0]);

// Detect the brand name (mc, hm) and country code (kw, sa, ae) from the site name.
$site_code = substr($domain, 0, -2);
$country_code = substr($domain, -2);

$f = fopen('/home/alshaya/debug.txt', 'a');
fwrite($f, "\n");
fwrite($f, 'TEST VBO');
fwrite($f, "\n");
fwrite($f, $_SERVER['HTTP_HOST'] . ' - ' . $domain . ' - ' . $site_code . ' - ' . $country_code);
fwrite($f, "\n");
fwrite($f, 'group ' . $_ENV['AH_SITE_GROUP'] . ' - env ' . $_ENV['AH_SITE_ENVIRONMENT']);
fwrite($f, "\n");
fclose($f);

if ($site_code == 'vb') {
  $f = fopen('/home/alshaya/debug.txt', 'a');
  fwrite($f, 'Enter the condition and run exec');
  fwrite($f, dirname(__FILE__) . '/post-install.sh "' . $_ENV['AH_SITE_ENVIRONMENT'] . '" "' . $domain . '" "' . $site_code . '" "' . $country_code . '"');
  fwrite($f, "\n");
  fclose($f);

  exec(dirname(__FILE__) . '/post-install.sh "' . $_ENV['AH_SITE_ENVIRONMENT'] . '" "' . $domain . '" "' . $site_code . '" "' . $country_code . '"');
}