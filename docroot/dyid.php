<?php

/**
 * @file
 * Contains the dyid cookie logic.
 */

// Store the info of dyid and dyid_server cookie.
$dyid_cookie = filter_input(INPUT_COOKIE, '_dyid') ?? '';
$dyid_server_cookie = filter_input(INPUT_COOKIE, '_dyid_server') ?? '';

// Double check that _dyid cookie exists and that _dyid_server cookie
// does not exist.
if ($dyid_cookie && !$dyid_server_cookie) {
  // Add _dyid_server cookie.
  // The name of the cookie.
  // The value of the cookie.
  // The time the cookie expires.
  // The path on the server in which the cookie will be available on.
  // The domain that the cookie is available to.
  // Whether the cookie is secure.
  setcookie('_dyid_server', $dyid_cookie, [
    'expires' => strtotime('+1 year'),
    'path' => '/',
    'domain' => NULL,
    'secure' => TRUE,
    'httponly' => FALSE,
  ]);
}
