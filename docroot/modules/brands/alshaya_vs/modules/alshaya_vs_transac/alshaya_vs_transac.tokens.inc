<?php

/**
 * @file
 * The token file.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_tokens().
 */
function alshaya_vs_transac_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  $brand_name = t("Victoria's Secret", [], ['context' => 'alshaya_custom_meta']);;

  // If super category feature is enabled.
  if (\Drupal::config('alshaya_super_category.settings')->get('status')) {
    $super_category = \Drupal::service('alshaya_acm_product_category.product_category_tree')->getCategoryTermRequired();
    if (!empty($super_category)) {
      $brand_name = $super_category['label'];
    }
  }

  if ($type == 'custom_meta') {
    foreach ($tokens as $name => $original) {
      // Find the desired token by name.
      switch ($name) {
        case 'brand_name':
          $replacements[$original] = $brand_name;
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
