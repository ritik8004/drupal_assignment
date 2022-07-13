<?php
// phpcs:ignoreFile

/**
 * @file
 * The index.php file generated while setting up project.
 */

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

// DRUPAL_ROOT is used by Drupal sites.inc.
const DRUPAL_ROOT = __DIR__ . '/../..';

require dirname(__DIR__) . '/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
  umask(0000);

  Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? FALSE) {
  Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? FALSE) {
  Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
