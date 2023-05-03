<?php
// phpcs:ignoreFile

require_once  '/var/www/html/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$data = Yaml::parse(file_get_contents('/var/www/html/blt/alshaya_sites.yml'));

print implode(' ', array_keys($data['sites']));
