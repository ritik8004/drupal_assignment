<?php

/**
 * @file
 * The token file.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_tokens().
 */
function alshaya_bbw_transac_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'custom_meta') {
    foreach ($tokens as $name => $original) {
      // Find the desired token by name.
      switch ($name) {
        case 'brand_name':
          $replacements[$original] = t('Bath and body works', [], ['context' => 'alshaya_custom_meta']);
          break;

        case 'usp1':
          $replacements[$original] = t('Free Delivery on orders over AED 99', [], ['context' => 'alshaya_custom_meta']);
          break;

        case 'usp2':
          $replacements[$original] = t('Easy returns', [], ['context' => 'alshaya_custom_meta']);
          break;

        case 'usp3':
          $replacements[$original] = t('Click & Collect', [], ['context' => 'alshaya_custom_meta']);
          break;
      }
    }
  }

  return $replacements;
}
