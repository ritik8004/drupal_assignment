<?php

/**
 * @file
 * Include file for RCS Placeholders Tokens.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function rcs_placeholders_token_info() {
  $info = [];

  $info['types']['rcs'] = [
    'name' => t('RCS'),
    'description' => t('Tokens related to RCS.'),
  ];

  $info['tokens']['rcs']['product-path-prefix'] = [
    'name' => t('RCS product path prefix'),
    'description' => t('The configured path prefix for RCS products.'),
  ];

  $info['tokens']['rcs']['category-path-prefix'] = [
    'name' => t('RCS category path prefix'),
    'description' => t('The configured path prefix for RCS categories.'),
  ];

  $info['tokens']['rcs']['promotion-path-prefix'] = [
    'name' => t('RCS promotion path prefix'),
    'description' => t('The configured path prefix for RCS promotion.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function rcs_placeholders_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  // We only deal with RCS tokens.
  if ($type !== 'rcs') {
    return [];
  }
  $config = \Drupal::config('rcs_placeholders.settings');

  $replacements = [];

  foreach ($tokens as $name => $original) {
    switch ($name) {
      case 'product-path-prefix':
        $replacements[$original] = $config->get('product.path_prefix');
        break;

      case 'category-path-prefix':
        $replacements[$original] = $config->get('category.path_prefix');
        break;

      case 'promotion-path-prefix':
        $replacements[$original] = $config->get('promotion.path_prefix');
        break;
    }
  }

  return $replacements;
}
