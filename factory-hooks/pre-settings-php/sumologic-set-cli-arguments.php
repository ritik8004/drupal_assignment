<?php

/**
 * @file
 * Add logging information for drush requests in drupal-requests.log on Acquia.
 *
 * By default these requests show up with no REQUEST_METHOD or URI, which can
 * make splitting them up in sumologic very hard.
 */

if (!function_exists('alshaya_get_cli_request_id')) {

  /**
   * Get request id for current request.
   *
   * @return string
   *   Unique UUID / request id.
   */
  function alshaya_get_cli_request_id() {
    static $request_id = NULL;

    if (is_null($request_id)) {
      $request_id = date('Ymd') . '-' . sprintf('%05d-%05d-%05d-%05d',
          random_int(0, 99999),
          random_int(0, 99999),
          random_int(0, 99999),
          random_int(0, 99999)
        );
    }

    return $request_id;
  }

}

$ah_env = getenv('AH_SITE_ENVIRONMENT');
if ($ah_env && $ah_env !== 'ide' && PHP_SAPI === 'cli') {
  // Set the `request method`.
  putenv('REQUEST_METHOD=CLI');
  // Set the `domain`.
  putenv('HTTP_HOST=' . $_SERVER['HTTP_HOST']);

  if (array_key_exists('argv', $_SERVER)) {
    $cli_args = $_SERVER['argv'];
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

    // Set request_id in request.
    $uri .= ' cli_request_id=' . alshaya_get_cli_request_id();

    // Set the `request uri`.
    putenv('REQUEST_URI=' . $uri);
  }
}
