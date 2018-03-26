<?php

namespace Drupal\alshaya_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\alshaya_main_menu\ProductCategoryTree;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides alshaya main menu super category block.
 *
 * @Block(
 *   id = "alshaya_main_menu_super_category",
 *   admin_label = @Translation("Alshaya main menu super category")
 * )
 */
class AlshayaMainMenuSuperCategoryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Array of terms for cache bubbling up.
   *
   * @var array
   */
  protected $cacheTags = [];

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_main_menu\ProductCategoryTree
   */
  protected $productCateoryTree;

  /**
   * AlshayaMainMenuSuperCategoryBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\alshaya_main_menu\ProductCategoryTree $product_category_tree
   *   Product category tree.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProductCategoryTree $product_category_tree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCateoryTree = $product_category_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_main_menu.product_category_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $term_data = $this->productCateoryTree->getCategoryRootTerms();

    // If no data, no need to render the block.
    if (empty($term_data)) {
      return [];
    }

    // Get current term from route.
    $term = $this->productCateoryTree->getCategoryTermFromRoute();
    // Get all parents of the given term.
    if ($term instanceof TermInterface) {
      $parents = $this->productCateoryTree->getCategoryTermParents($term);

      // @todo: Deal with default case.
      if (!empty($parents)) {
        /* @var \Drupal\taxonomy\TermInterface $root_parent_term */
        foreach ($parents as $parent) {
          if (isset($term_data[$parent->id()])) {
            $term_data[$parent->id()]['class'] = 'active';
          }
        }
      }
    }

    return [
      '#theme' => 'alshaya_main_menu_top_level',
      '#term_tree' => $term_data,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Discard cache for the block once a term gets updated.
    $this->cacheTags[] = ProductCategoryTree::VOCABULARY_ID;

    return Cache::mergeTags(
      parent::getCacheTags(),
      $this->cacheTags
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}
