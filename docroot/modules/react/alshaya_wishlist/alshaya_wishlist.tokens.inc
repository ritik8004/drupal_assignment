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

        // Check for the current language and if it's not english default,
        // we need to get the language specific configs and set the
        // wishlist_label based on it. This is because for VS and WES we were
        // not getting the language specific configs and thus it's causing
        // issues on the my wishlist page for the translations.
        // @todo this is, however, a temporary fix and we need to find out
        // the root cause of the problem behind this for VS and WES.
        $langManager = \Drupal::languageManager();
        $langCode = $langManager->getCurrentLanguage()->getId();
        if ($langCode !== 'en') {
          $config = $langManager->getLanguageConfigOverride($langCode, 'alshaya_wishlist.settings');
        }

        $bubbleable_metadata->addCacheableDependency($config);
        $replacements[$original] = $config->get('wishlist_label');
        break;
    }
  }

  return $replacements;
}
