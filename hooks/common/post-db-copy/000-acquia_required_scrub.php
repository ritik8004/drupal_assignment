#!/usr/bin/env php
<?php

/**
 * @file
 * Scrubs a site after its database has been copied.
 *
 * This happens on staging, not on site duplication; duplication does not call
 * the 'db-copy' Acquia Hosting task which executes this hook.
 */

if (empty($argv[3])) {
  echo "Error: Not enough arguments.\n";
  exit(1);
}

// AH site group.
$site = $argv[1];
// AH site env.
$env = $argv[2];
// Database name.
$db_role = $argv[3];

$docroot = sprintf('/var/www/html/%s.%s/docroot', $site, $env);

include_once $docroot . '/sites/g/sites.inc';
$sites_json = gardens_site_data_load_file();
if (!$sites_json) {
  // If the file exists, and cannot be loaded, exit with an error.
  if (file_exists(gardens_site_data_get_filepath())) {
    fwrite(STDERR, "The site registry could not be loaded from the server.\n");
    exit(1);
  }
  // Exit gracefully if the sites.json is not available. That usually
  // indicates that the code is running on a non-acsf environment.
  fwrite(STDERR, "The site registry does not exist; this doesn't look like an ACSF environment.\n");
  exit(0);
}

fwrite(STDERR, sprintf("Scrubbing site database: site: %s; env: %s; db_role: %s;\n", $site, $env, $db_role));

$new_domain = FALSE;
foreach ($sites_json['sites'] as $site_domain => $site_info) {
  if ($site_info['conf']['acsf_db_name'] === $db_role && !empty($site_info['flags']['preferred_domain'])) {
    $new_domain = $site_domain;
    fwrite(STDERR, "Site domain: $new_domain;\n");

    // When the site being staged has different a code than its source, the
    // original code will be deployed on the update environment to ensure that
    // the scrubbing process will not fail due to code / data structure
    // differences.
    if (!empty($site_info['flags']['staging_exec_on'])) {
      $env = $site_info['flags']['staging_exec_on'];
      $docroot = sprintf('/var/www/html/%s.%s/docroot', $site, $env);
    }
    break;
  }
}
if (!$new_domain) {
  error('Could not find the domain that belongs to the site.');
}

// Create a cache directory for drush.
$cache_directory = sprintf('/mnt/tmp/%s.%s/drush_tmp_cache/%s', $site, $env, md5($new_domain));
// Acquia rules disallow shell_exec() with dynamic arguments.
// phpcs:disable
shell_exec(sprintf('mkdir -p %s', escapeshellarg($cache_directory)));
// phpcs:enable

// Explicitly run a cache-rebuild before anything else.
$cache_rebuild = sprintf(
  'DRUSH_PATHS_CACHE_DIRECTORY=%1$s CACHE_PREFIX=%1$s AH_SITE_ENVIRONMENT=%2$s \drush8 -r %3$s -l %4$s -y cache-rebuild 2>&1',
  escapeshellarg($cache_directory),
  escapeshellarg($env),
  escapeshellarg($docroot),
  escapeshellarg('https://' . $new_domain)
);
fwrite(STDERR, "Executing: $cache_rebuild;\n");
$result = 0;
$output = [];
// Acquia rules disallow exec() with dynamic arguments.
// phpcs:disable
exec($cache_rebuild, $output, $result);
// phpcs:enable
print implode("\n", $output);
fwrite(STDERR, "Command execution returned status code: $result!\n");

// Execute the scrub. If we execute code on the update environment (as per
// above), we must change AH_SITE_ENVIRONMENT to match the docroot during
// execution; see sites.php.
$command = sprintf(
  'DRUSH_PATHS_CACHE_DIRECTORY=%1$s CACHE_PREFIX=%1$s AH_SITE_ENVIRONMENT=%2$s \drush8 -r %3$s -l %4$s -y acsf-site-scrub 2>&1',
  escapeshellarg($cache_directory),
  escapeshellarg($env),
  escapeshellarg($docroot),
  escapeshellarg('https://' . $new_domain)
);
fwrite(STDERR, "Executing: $command;\n");

$result = 0;
$output = [];
// Acquia rules disallow exec() with dynamic arguments.
// phpcs:disable
exec($command, $output, $result);
// phpcs:enable
print implode("\n", $output);

// Clean up the drush cache directory.
// Acquia rules disallow exec() with dynamic arguments.
// phpcs:disable
shell_exec(sprintf('rm -rf %s', escapeshellarg($cache_directory)));
// phpcs:enable

if ($result) {
  fwrite(STDERR, "Command execution returned status code: $result!\n");
  exit($result);
}
