<?php
// phpcs:ignoreFile

$brand = isset($argv, $argv[1]) ? $argv[1] : '';
$env = isset($argv, $argv[2]) ? $argv[2] : '';
$app_id = isset($argv, $argv[3]) ? $argv[3] : '';
$app_secret_admin = isset($argv, $argv[4]) ? $argv[4] : '';

if (empty($brand) || empty($env) || empty($app_id) || empty($app_secret_admin)) {
  print 'Please ensure you have passed [brand] [env] [app_id] [app_secret_admin] as arguments to script.' . PHP_EOL . PHP_EOL;
  die();
}

$prefix = $env . '_' . $brand;
