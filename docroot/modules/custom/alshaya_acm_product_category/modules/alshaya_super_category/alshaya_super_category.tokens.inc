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

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'meta_title':
          $current_route = \Drupal::routeMatch();
          switch ($current_route->getRouteName()) {
            // If term page.
            case 'entity.taxonomy_term.canonical':
              if (!empty($term = $current_route->getParameter('taxonomy_term'))) {
                // Get super category term.
                $super_category = _alshaya_super_category_get_super_category_for_term($term);
                if ($super_category instanceof TermInterface) {
                  $meta_tags = \Drupal::service('metatag.manager')->tagsFromEntityWithDefaults($super_category);
                  // We just removing the the token/pattern for the super
                  // category or we will stuck in infinite loop.
                  $meta_tags['title'] = str_replace('[supercategory:meta_title]', '', $meta_tags['title']);
                  $meta_title = \Drupal::service('token')->replace($meta_tags['title'], ['term' => $super_category, 'is_super_category' => TRUE]);
                  $replacements[$original] = $meta_title;
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
                    $meta_tags = \Drupal::service('metatag.manager')->tagsFromEntityWithDefaults($super_category);
                    // We just removing the the token/pattern for the super
                    // category or we will stuck in infinite loop.
                    $meta_tags['title'] = str_replace('[supercategory:meta_title]', '', $meta_tags['title']);
                    $meta_title = \Drupal::service('token')->replace($meta_tags['title'], ['term' => $super_category]);
                    $replacements[$original] = $meta_title;
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
                      $meta_tags = \Drupal::service('metatag.manager')->tagsFromEntityWithDefaults($super_category);
                      // We just removing the the token/pattern for the super
                      // category or we will stuck in infinite loop.
                      $meta_tags['title'] = str_replace('[supercategory:meta_title]', '', $meta_tags['title']);
                      $meta_title = \Drupal::service('token')->replace($meta_tags['title'], ['term' => $super_category]);
                      $replacements[$original] = $meta_title;
                    }
                  }
                }
                else {
                  // If normal advanced pages.
                  if (!empty($tid = alshaya_super_category_get_default_term())) {
                    if (!empty($term = Term::load($tid))) {
                      $super_category = _alshaya_super_category_get_super_category_for_term($term);
                      if ($super_category instanceof TermInterface) {
                        $meta_tags = \Drupal::service('metatag.manager')->tagsFromEntityWithDefaults($super_category);
                        // We just removing the the token/pattern for the super
                        // category or we will stuck in infinite loop.
                        $meta_tags['title'] = str_replace('[supercategory:meta_title]', '', $meta_tags['title']);
                        $meta_title = \Drupal::service('token')->replace($meta_tags['title'], ['term' => $super_category]);
                        $replacements[$original] = $meta_title;
                      }
                    }
                  }
                }
              }
              elseif ($node instanceof Node && $node->bundle() == 'acq_promotion') {
                // If promotional node type.
                if (!empty($tid = alshaya_super_category_get_default_term())) {
                  if (!empty($term = Term::load($tid))) {
                    $super_category = _alshaya_super_category_get_super_category_for_term($term);
                    if ($super_category instanceof TermInterface) {
                      $meta_tags = \Drupal::service('metatag.manager')->tagsFromEntityWithDefaults($super_category);
                      // We just removing the the token/pattern for the super
                      // category or we will stuck in infinite loop.
                      $meta_tags['title'] = str_replace('[supercategory:meta_title]', '', $meta_tags['title']);
                      $meta_title = \Drupal::service('token')->replace($meta_tags['title'], ['term' => $super_category]);
                      $replacements[$original] = $meta_title;
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
