<?php

/**
 * @file
 * Contains tokens provided by the module..
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function alshaya_wishlist_token_info() {
  $info = [];
  $info['types']['alshaya_wishlist'] = [
    'name' => t('Wishlist Feature'),
    'description' => t('List of tokens for wishlist feature as per current brand.'),
  ];

  $info['tokens']['alshaya_wishlist']['wishlist_label'] = [
    'name' => t('Wishlist Label'),
    'description' => t('Token for wishlist label.'),
  ];
  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_wishlist_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type !== 'alshaya_wishlist') {
    return $replacements;
  }

  foreach ($tokens as $name => $original) {
    switch ($name) {
      case 'wishlist_label':
        $config = \Drupal::config('alshaya_wishlist.settings');
        $bubbleable_metadata->addCacheableDependency($config);
        $replacements[$original] = $config->get('wishlist_label');
        break;
    }
  }

  return $replacements;
}
