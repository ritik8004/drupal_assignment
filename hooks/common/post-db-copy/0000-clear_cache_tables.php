#!/usr/bin/env php
<?php

/**
 * @file
 * Clear data from cache tables after database has been copied.
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

fwrite(STDERR, sprintf("Clearing cache tables: site: %s; env: %s; db_role: %s;\n", $site, $env, $db_role));

include_once $docroot . '/sites/g/sites.inc';
$sites_json = gardens_site_data_load_file();
if (!$sites_json) {
  error('The site registry could not be loaded from the server.');
}
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
    }
    break;
  }
}
if (!$new_domain) {
  error('Could not find the domain that belongs to the site.');
}

$docroot = sprintf('/var/www/html/%s.%s/docroot', $site, $env);

// Create a cache directory for drush.
$cache_directory = sprintf('/mnt/tmp/%s.%s/drush_tmp_cache/%s', $site, $env, md5($new_domain));
shell_exec(sprintf('mkdir -p %s', escapeshellarg($cache_directory)));

// Execute database cleanup. If we execute code on the update environment (as per
// above), we must change AH_SITE_ENVIRONMENT to match the docroot during
// execution; see sites.php.
$command = "db=`CACHE_PREFIX=$cache_directory AH_SITE_ENVIRONMENT=$env \drush8 -r $docroot -l https://$new_domain sql-connect` ; \$db --disable-column-names -e \"SHOW TABLES LIKE 'cache%'\" | xargs -I cache_table \$db -e \"TRUNCATE TABLE cache_table\"";
fwrite(STDERR, "Executing: $command;\n");

$result = 0;
$output = [];
exec($command, $output, $result);
print implode("\n", $output);

// Clean up the drush cache directory.
shell_exec(sprintf('rm -rf %s', escapeshellarg($cache_directory)));

if ($result) {
  fwrite(STDERR, "Command execution returned status code: $result!\n");
  exit($result);
}

// Execute memcache flush.
$command = "/bin/echo -e 'flush_all' | nc -q1 \$(hostname -s) 11211";
fwrite(STDERR, "Executing: $command;\n");

$result = 0;
$output = [];
exec($command, $output, $result);
print implode("\n", $output);

if ($result) {
  fwrite(STDERR, "Command execution returned status code: $result!\n");
  exit($result);
}
