<?php

/**
 * @file
 * Alshaya master token file.
 */

/**
 * Implements hook_token_info().
 */
function alshaya_master_token_info() {
  $info = [];

  $info['types']['server'] = [
    'name' => t('Server vars'),
    'description' => t('Tokens from _SERVER.'),
  ];

  $info['tokens']['server']['HTTP_REFERER'] = [
    'name' => t('HTTP_REFERER'),
    'description' => t("Var from _SERVER"),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_master_tokens($type, $tokens, array $data = [], array $options = []) {
  $replacements = [];
  $sanitize = !empty($options['sanitize']);

  // Text format tokens.
  if ($type == 'server') {

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'HTTP_REFERER':
          $replacements[$original] = \Drupal::request()->server->get('HTTP_REFERER');
          if ($sanitize) {
            $replacements[$original] = filter_xss($replacements[$original]);
          }
          break;
      }
    }
  }

  return $replacements;
}
