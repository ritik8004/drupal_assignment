<?php

/**
 * @file
 * Add logging information for drush requests in drupal-requests.log on Acquia.
 *
 * By default these requests show up with no REQUEST_METHOD or URI, which can
 * make splitting them up in sumologic very hard.
 */

if (isset($_ENV['AH_SITE_ENVIRONMENT']) && PHP_SAPI === 'cli') {
  // Set the `request method`.
  putenv('REQUEST_METHOD=CLI');
  // Set the `domain`.
  putenv('HTTP_HOST=' . $_SERVER['HTTP_HOST']);

  if (function_exists('drush_get_context')) {
    $cli_args = drush_get_context('argv');
    $cli_args[0] = 'drush';

    // Ensure each argument is wrapped in quotes.
    $cli_args = array_map(function ($value) {
      $escaped = escapeshellarg($value);
      return ("'$value'" === $escaped) ? $value : $escaped;
    }, $cli_args);

    // Prepare the uri.
    $uri = implode(' ', $cli_args);

    // Get users ip.
    $output = shell_exec('who -m');
    if (preg_match('#\((.*?)\)#', $output, $match)) {
      // Set users ip in msg.
      $uri .= ' src_ip=' . $match[1];
    }

    // Set the `request uri`.
    putenv('REQUEST_URI=' . $uri);
  }
}
