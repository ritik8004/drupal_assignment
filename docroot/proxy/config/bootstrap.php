<?php

/**
 * @file
 * Bootstrap file generated while setting up project.
 *
 * This file is modified to suit Alshaya specific needs. We read environment
 * variables from .env file in server home directory.
 */

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

// Load the .env files from CODE.
(new Dotenv(FALSE))->loadEnv(dirname(__DIR__) . '/.env');

$home = '/home/vagrant';
if (getenv('AH_SITE_ENVIRONMENT')) {
  $home = $_SERVER['HOME'];
}
elseif (getenv('LANDO')) {
  $home = '/app/local_home';
}
elseif (getenv('IS_DDEV_PROJECT')) {
  $home = '/var/www/html/local_home';
}

$env = 'local';

if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $env = $_ENV['AH_SITE_ENVIRONMENT'];
}
elseif (getenv('TRAVIS') || getenv('CI_BUILD_ID')) {
  $env = 'travis';
}

// Ensure we use development mode in local.
if ($env === 'local') {
  $_SERVER['APP_ENV'] = 'dev';
}

if (file_exists($home . '/settings/.env')) {
  // Load the .env files from Server Home.
  (new Dotenv(FALSE))->loadEnv($home . '/settings/.env');
}

$_SERVER += $_ENV;
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? NULL) ?: 'prod';
$_SERVER['APP_DEBUG'] ??= $_ENV['APP_DEBUG'] ?? 'prod' !== $_SERVER['APP_ENV'];
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (int) $_SERVER['APP_DEBUG'] || filter_var($_SERVER['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
