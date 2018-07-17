<?php

/**
 * @file
 * Alshaya super category token file.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\Entity\Term;
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

  $info['tokens']['supercategory']['title'] = [
    'name' => t('Title'),
    'description' => t('Title of super category.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function alshaya_super_category_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'supercategory') {

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'title':
          $current_route = \Drupal::routeMatch();
          switch ($current_route->getRouteName()) {
            // If term page.
            case 'entity.taxonomy_term.canonical':
              if (!empty($term = $current_route->getParameter('taxonomy_term'))) {
                // Get super category term.
                $super_category = _alshaya_super_category_get_super_category_for_term($term);
                if ($super_category instanceof TermInterface) {
                  $replacements[$original] = $super_category->label();
                }
              }
              break;

            // If node page.
            case 'entity.node.canonical':
              $node = $current_route->getParameter('node');
              // For 'product' node bundle.
              if ($node instanceof Node && $node->bundle() == 'acq_product') {
                $referenced_tid = $node->get('field_category')->first()->getString();
                if (!empty($term = Term::load($referenced_tid))) {
                  $super_category = _alshaya_super_category_get_super_category_for_term($term);
                  if ($super_category instanceof TermInterface) {
                    $replacements[$original] = $super_category->label();
                  }
                }
              }
              // If 'advanced_page' node bundle.
              elseif ($node instanceof Node && $node->bundle() == 'advanced_page') {
                $department_pages = alshaya_advanced_page_get_pages();
                // If department page.
                if ($tid = array_search($node->id(), $department_pages)) {
                  if (!empty($term = Term::load($tid))) {
                    $super_category = _alshaya_super_category_get_super_category_for_term($term);
                    if ($super_category instanceof TermInterface) {
                      $replacements[$original] = $super_category->label();
                    }
                  }
                }
                else {
                  // If normal advanced pages.
                  if (!empty($tid = alshaya_super_category_get_default_term())) {
                    if (!empty($term = Term::load($tid))) {
                      $super_category = _alshaya_super_category_get_super_category_for_term($term);
                      if ($super_category instanceof TermInterface) {
                        $replacements[$original] = $super_category->label();
                      }
                    }
                  }
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
