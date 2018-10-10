<?php
/**
 * @file
 * Customisation of sites.php for local dev env.
 */

use Symfony\Component\Yaml\Yaml;

if (!isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $sites['default'] = 'g';
  $sites['127.0.0.1'] = 'g';

  $data = Yaml::parse(file_get_contents(__DIR__ . '/../../blt/local.blt.yml'));

  foreach ($data['sites'] as $site_code => $site_info) {
    $sites['local.alshaya-' . $site_code . '.com'] = 'g';
  }
}
