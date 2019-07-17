<?php

/**
 * @file
 * Token related functions for the alshaya_seo_transac.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Implements hook_token_info().
 */
function alshaya_seo_transac_token_info() {
  // Name and machine_name/key do not match. It is not changed intentionally
  // to avoid hook_update. Functionality was to show all categories first but
  // later it was changed to just the leaf level name.
  $info['tokens']['node']['category_names'] = [
    'name' => t('Category Name'),
    'description' => t('Leaf level category used in breadcrumb.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_seo_transac_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if (isset($tokens['category_names']) && !empty($data['node']) && $data['node'] instanceof NodeInterface) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $data['node'];

    // Get all categories for the node.
    if ($field_category = $node->get('field_category')) {
      $term_list = $field_category->getValue();
      if ($parents = \Drupal::service('alshaya_acm_product.category_helper')->getBreadcrumbTermList($term_list)) {
        foreach ($parents as $term) {
          if ($term instanceof TermInterface) {
            $term = \Drupal::service('entity.repository')->getTranslationFromContext($term);
            $replacements[$tokens['category_names']] = $term->getName();
            break;
          }
        }
      }
    }
  }

  return $replacements;
}
