<?php
/**
 * Script helper file to parse yaml and get required data.
 */

$autoloader = require_once __DIR__ . '/../docroot/autoload.php';

use Symfony\Component\Yaml\Yaml;

$site = $argv[1];
$info_required = $argv[2];

$data = stream_get_contents(STDIN);
$data = explode(PHP_EOL, $data);
$yaml_data = '';

$start_reading = FALSE;

foreach ($data as $index => $line) {
  if ($line == $site) {
    $start_reading = TRUE;
    continue;
  }

  if ($start_reading) {
    if (strpos($line, ' ') !== 0) {
      break;
    }

    $yaml_data .= substr($line, 2) . PHP_EOL;
  }
}

$array = Yaml::parse($yaml_data);

if (is_array($array)) {
  if ($info_required == 'db_role') {
    print $array['name'];
  }
  elseif ($info_required == 'url') {
    print reset($array['domains']);
  }
}
