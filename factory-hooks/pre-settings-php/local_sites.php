<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Identifies local site host code.
 */

global $host_site_code;

// Get site code from site uri.
if (!empty($_SERVER['HTTP_HOST'])) {
  $hostname_parts = explode('.', $_SERVER['HTTP_HOST']);
  $host_site_code = str_replace('alshaya-', '', $hostname_parts[1]);
}
else {
  $host_site_code = 'default_local';
  foreach ($_SERVER['argv'] as $arg) {
    preg_match('/[\\S|\\s|\\d|\\D]*local.alshaya-(\\S*).com/', $arg, $matches);
    if (!empty($matches)) {
      $host_site_code = $matches[1];
      break;
    }
  }
}
