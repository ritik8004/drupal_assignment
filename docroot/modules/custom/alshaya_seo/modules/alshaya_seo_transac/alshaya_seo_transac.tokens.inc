<?php

/**
 * @file
 * Token related functions for the alshaya_seo_transac.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\node\NodeInterface;

/**
 * Implements hook_token_info().
 */
function alshaya_seo_transac_token_info() {
  $info['tokens']['node']['category_names'] = [
    'name' => t('Category Names'),
    'description' => t('Comma separated list of all categories referenced in the node.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_seo_transac_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  if ($type == 'node' && !empty($data['node']) && $data['node'] instanceof NodeInterface) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $data['node'];
    if (isset($tokens['category_names'])) {
      // Get all categories for the node.
      if ($field_category = $node->get('field_category')) {
        $term_list = $field_category->getValue();
        if ($parents = \Drupal::service('alshaya_acm_product_category.product_category_tree')->getBreadcrumbTermList($term_list)) {
          $replacements[$tokens['category_names']] = reset($parents)->getName() ?? '';
        }
      }
    }
  }
  return $replacements;
}
