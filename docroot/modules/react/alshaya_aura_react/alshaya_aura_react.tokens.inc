<?php

/**
 * @file
 * Token related functions for the alshaya_aura_react.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function alshaya_aura_react_token_info() {
  $info = [];

  $info['types']['alshaya_aura'] = [
    'name' => t('Alshaya Aura'),
    'description' => t('Tokens from Alshaya Aura.'),
  ];

  $info['tokens']['alshaya_aura']['currency'] = [
    'name' => t('Currency'),
    'description' => t('Website currency'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_aura_react_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'alshaya_aura') {
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'currency':
          $currency_code = \Drupal::config('acq_commerce.currency')->get('currency_code');
          $replacements[$original] = $currency_code;
          break;
      }
    }
  }

  return $replacements;
}
