<?php

/**
 * @file
 * Alshaya super category token file.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\taxonomy\TermInterface;
use Drupal\node\Entity\Node;

/**
 * Implements hook_token_info().
 */
function alshaya_super_category_token_info() {
  $info = [];

  $info['types']['supercategory'] = [
    'name' => t('Super category'),
    'description' => t('Tokens from super category.'),
  ];

  $info['tokens']['supercategory']['meta_title'] = [
    'name' => t('Meta title'),
    'description' => t('Meta title of super category.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_super_category_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'supercategory') {
    if (!\Drupal::config('alshaya_super_category.settings')->get('status')) {
      \Drupal::logger('alshaya_super_category')
        ->notice('super category feature is not enabled, But trying to use for token replacement.');
      return;
    }

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'meta_title':
          $current_route = \Drupal::routeMatch();
          switch ($current_route->getRouteName()) {
            // If term page.
            case 'entity.taxonomy_term.canonical':
            case 'entity.node.canonical':
              $valid_term = $current_route->getParameter('taxonomy_term');

              $valid_node = FALSE;
              if ($node = $current_route->getParameter('node')) {
                $valid_node = $node instanceof Node && in_array($node->bundle(), [
                  'acq_product',
                  'advanced_page',
                  'acq_promotion',
                ]);
              }

              if ($valid_term || $valid_node) {
                $super_category = _alshaya_super_category_get_super_category_for_term();
                if ($super_category instanceof TermInterface) {
                  $replacements[$original] = $super_category->label();
                }
              }
              break;
          }
          break;
      }
    }
  }

  return $replacements;
}
