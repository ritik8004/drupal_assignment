<?php

namespace Drupal\alshaya_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides alshaya main menu block.
 *
 * @Block(
 *   id = "alshaya_main_menu",
 *   admin_label = @Translation("Alshaya main menu")
 * )
 */
class AlshayaMainMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Vocabulary processed data.
   *
   * @var array
   */
  protected $termData = [];

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
   * AlshayaMegaMenuBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_manager, ProductCategoryTree $product_category_tree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->termStorage = $entity_manager->getStorage('taxonomy_term');
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
      $container->get('entity_type.manager'),
      $container->get('alshaya_acm_product_category.product_category_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the term object from current route.
    $term = $this->productCateoryTree->getCategoryTermFromRoute();

    // @todo: set default parent_id for second level category menu.
    $parent_id = 0;
    // Get the term id from the current path, and display only the related
    // second level child terms.
    if ($term instanceof TermInterface && $parents = $this->productCateoryTree->getCategoryTermParents($term)) {
      // Get the top level parent id if parent exists.
      $parents = array_keys($parents);
      $parent_id = empty($parents) ? $term->id() : end($parents);
    }

    // Child terms of given parent term id.
    $term_data = $this->productCateoryTree->getCategoryTreeCached($parent_id);

    // If no data, no need to render the block.
    if (empty($term_data)) {
      return [];
    }

    // Get all parents of the given term.
    if ($term instanceof TermInterface) {
      $parents = $this->productCateoryTree->getCategoryTermParents($term);

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
      '#theme' => 'alshaya_main_menu_level1',
      '#term_tree' => $term_data,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Add department page node type cache tag.
    // This is custom cache tag and cleared in hook_presave in department
    // module.
    $this->cacheTags[] = 'node_type:department_page';

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
