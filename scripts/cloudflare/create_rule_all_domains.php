<?php
// @codingStandardsIgnoreFile

require_once 'common.php';

$domains = get_domains();

foreach ($domains as $domain => $zone) {
  $command = 'php ' . __DIR__ . '/create_page_rules.php ' . $domain;
  echo $command . PHP_EOL;
  $output = shell_exec($command);
  echo $output;
  echo PHP_EOL;
}
