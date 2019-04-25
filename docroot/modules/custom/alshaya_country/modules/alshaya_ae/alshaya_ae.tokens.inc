<?php

/*
 * @file
 * Token related functions for the alshaya_seo.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_tokens().
 */
function alshaya_ae_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'custom_meta') {
    foreach ($tokens as $name => $original) {
      // Find the desired token by name.
      switch ($name) {
        case 'country':
          $replacements[$original] = t('UAE', [], ['context' => 'alshaya_custom_meta']);
          break;

        case 'city1':
          $replacements[$original] = t('Dubai', [], ['context' => 'alshaya_custom_meta']);
          break;

        case 'city2':
          $replacements[$original] = t('Abu dhabi', [], ['context' => 'alshaya_custom_meta']);
          break;
      }
    }
  }

  return $replacements;
}
