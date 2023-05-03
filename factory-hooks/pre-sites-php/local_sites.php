<?php
// phpcs:ignoreFile

/**
 * @file
 * Customisation of sites.php for local dev env. Identifies local site host code.
 */

use Symfony\Component\Yaml\Yaml;

global $host_site_code;

if (!(getenv('AH_SITE_ENVIRONMENT'))) {
  $sites['default'] = 'g';
  $sites['127.0.0.1'] = 'g';

  $data = Yaml::parse(file_get_contents(__DIR__ . '/../../blt/alshaya_sites.yml'));

  foreach ($data['sites'] as $site_code => $site_info) {
    $sites['local.alshaya-' . $site_code . '.com'] = 'g';

    if (getenv('LANDO') || getenv('IS_DDEV_PROJECT')) {
      $sites[$site_code . '.alshaya.lndo.site'] = 'g';
      $sites[$site_code . '.varnish.alshaya.lndo.site'] = 'g';
    }

    if (getenv('IS_DDEV_PROJECT')) {
      $sites[$site_code . '.alshaya.ddev.site'] = 'g';
    }
  }

  // Web requests.
  if (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'alshaya') > -1) {
    $hostname_parts = explode('.', $_SERVER['HTTP_HOST']);
  }
  // Drush requests.
  else {
    foreach ($_SERVER['argv'] as $arg) {
      $url = null;

      if (strpos($arg, '--uri') > -1) {
        $url = str_replace('--uri=', '', $arg);
      }
      elseif (strpos($arg, 'local.alshaya-') > -1 || strpos($arg, '.site') > -1) {
        $url = $arg;
      }

      if (isset($url) && str_contains($url, 'alshaya')) {
        $url = str_replace('https://', '', $url);
        $url = str_replace('http://', '', $url);

        if (strpos($url, 'alshaya') > -1) {
          $hostname_parts = explode('.', $url);
        }

        break;
      }
    }
  }

  $host_site_code = 'default_local';

  // Support LANDO and Vagrant both.
  if (isset($hostname_parts)) {
    $host_site_code = in_array('site', $hostname_parts)
      ? $hostname_parts[0]
      : str_replace('alshaya-', '', $hostname_parts[1]);
  }
}
elseif (getenv('AH_SITE_ENVIRONMENT') === 'ide') {
  // This is the way we could achieve multi-site in Cloud IDE.
  $host_site_code = trim(file_get_contents('/home/ide/project/site.txt'));

  $sites['default'] = 'g';
  $sites['127.0.0.1'] = 'g';
  $sites[getenv('SERVER_NAME')] = 'g';
  $sites[getenv('ACQUIA_APPLICATION_UUID') . '.web.ahdev.cloud'] = 'g';
}
