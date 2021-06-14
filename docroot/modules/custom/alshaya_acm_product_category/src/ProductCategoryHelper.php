<?php

namespace Drupal\alshaya_acm_product_category;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Class Product Category Helper.
 *
 * @package Drupal\alshaya_product_category
 */
class ProductCategoryHelper {

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaCategoryLhnBlock constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   */
  public function __construct(ProductCategoryTree $product_category_tree,
                              LanguageManagerInterface $language_manager,
                              RouteMatchInterface $route_match,
                              ModuleHandlerInterface $module_handler) {
    $this->productCategoryTree = $product_category_tree;
    $this->languageManager = $language_manager;
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Build LHN for Product Category for particular language.
   *
   * @param string $baseID
   *   Block base id.
   * @param \Drupal\taxonomy\TermInterface $term
   *   Taxonomy term.
   * @param string $langcode
   *   Language code.
   * @param string $cache_id
   *   (Optional)Cache name.
   */
  public function productCategoryBuild($baseID, TermInterface $term, $langcode, $cache_id = 'category_tree') {
    $parent_id = 0;

    $context = [
      'block' => $baseID,
      'term' => $term,
      'depth_offset' => 0,
    ];
    // Invoke the alter hook to allow all modules to update parent_id.
    $this->moduleHandler->alter('product_category_parent', $parent_id, $context);

    // Get the term tree.
    $term_data = $this->productCategoryTree->getCategoryTreeWithIncludeInMenu($langcode, $parent_id, $cache_id);

    // If no data, no need to render the block.
    if (empty($term_data)) {
      return [];
    }

    $lhn_tree = [];

    // Get all parents of the given term.
    $parents = $this->productCategoryTree->getCategoryTermParents($term);

    // If parent exists for the current term. Here doing
    // `$context['depth_offset'] + 1` as $parents array contains the current
    // term as well.
    if ((count($parents) - ($context['depth_offset'] + 1)) > 0) {
      // Get root parent term.
      /** @var \Drupal\taxonomy\Entity\Term $root_parent_term*/
      $root_parent_term = array_pop($parents);

      if (!empty($context['depth_offset'])) {
        $root_parent_term = array_pop($parents);
      }

      $lhn_tree = $term_data[$root_parent_term->id()]['child'];
    }
    elseif (!empty($context['depth_offset'])) {
      $parent_child_terms = !empty($term_data[key($parents)]['child']) ? $term_data[key($parents)]['child'] : [];
      $lhn_tree = count($parents) == 1 ? $term_data : $parent_child_terms;
      if (count($parents) == 1) {
        $context['depth_offset'] = 2;
      }
    }
    else {
      $parent_child_terms = !empty($term_data[key($parents)]['child']) ? $term_data[key($parents)]['child'] : [];
      $lhn_tree = count($parents) == 1 ? $parent_child_terms : [];
    }

    if (empty($lhn_tree)) {
      return [];
    }

    $lhn_tree = array_filter($lhn_tree, function ($tree_term) {
      return $tree_term['lhn'];
    });

    $build = [
      '#theme' => 'alshaya_lhn_tree',
      '#lhn_cat_tree' => $lhn_tree,
      '#current_term' => $term->id(),
      '#depth_offset' => $context['depth_offset'],
      '#current_term_parent_id' => $term->get('parent')->getString(),
    ];

    return $build;
  }

  /**
   * Get the term tree for 'product_category' vocabulary from cache or fresh.
   *
   * @param \Drupal\taxonomy\TermInterface $terms
   *   Taxonomy term.
   * @param string $langcode
   *   Language code.
   *
   * @return array
   *   Processed term data from cache if available or fresh.
   */
  public function productCategoryBuildMobile(TermInterface $terms, $langcode) {
    $mobile_app_utility = \Drupal::service('alshaya_mobile_app.utility');
    $used_keys = ['label', 'id', 'path', 'clickable', 'child'];
    $term_data = $this->productCategoryBuild('alshaya_product_list_lhn_block', $terms, $lancode, 'product_list');
    foreach ($term_data['#lhn_cat_tree'] as $term_id => $term_value) {
      foreach ($term_value as $key => $value) {
        if (!in_array($key, $used_keys)) {
          unset($term_data['#lhn_cat_tree'][$term_id][$key]);
        }
        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term_id);
        $taxonomy_term_trans = \Drupal::service('entity.repository')->getTranslationFromContext($term, $langcode);

        $term_data['#lhn_cat_tree'][$term_id]['deep_link'] = $mobile_app_utility->getDeepLink($taxonomy_term_trans);
        if ($key == 'child' && !empty($value)) {
          foreach ($value as $child_term_id => $child_term_value) {
            foreach ($child_term_value as $child_key => $child_value) {
              // Unset not used keys in mobile.
              if (!in_array($child_key, $used_keys)) {
                unset($term_data['#lhn_cat_tree'][$term_id][$key][$child_term_id][$child_key]);
              }
              $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($child_term_id);
              $taxonomy_term_trans = \Drupal::service('entity.repository')->getTranslationFromContext($term, $langcode);
              $term_data['#lhn_cat_tree'][$term_id][$key][$child_term_id]['deep_link'] = $mobile_app_utility->getDeepLink($taxonomy_term_trans);
              // We dont show third level child terms data in website.
              unset($term_data['#lhn_cat_tree'][$term_id][$key][$child_term_id]['child']);
            }
          }
        }
      }
    }
    return $term_data['#lhn_cat_tree'];
  }

}
