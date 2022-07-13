<?php
// phpcs:ignoreFile

require_once __DIR__ . '/../../../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$data = Yaml::parse(file_get_contents(__DIR__ . '/../../../blt/alshaya_local_sites.yml'));

print implode(' ', array_keys($data['sites']));
