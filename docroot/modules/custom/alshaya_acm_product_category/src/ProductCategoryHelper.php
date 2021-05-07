<?php

namespace Drupal\alshaya_acm_product_category;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

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
   * @param object $term
   *   Taxonomy term.
   * @param string $langcode
   *   Language code.
   */
  public function productCategoryBuild($baseID, $term, $langcode) {
    $parent_id = 0;

    $context = [
      'block' => $baseID,
      'term' => $term,
      'depth_offset' => 0,
    ];
    // Invoke the alter hook to allow all modules to update parent_id.
    $this->moduleHandler->alter('product_category_parent', $parent_id, $context);

    // Get the term tree.
    $term_data = $this->productCategoryTree->getCategoryTreeWithIncludeInMenu($langcode, $parent_id);

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

}
