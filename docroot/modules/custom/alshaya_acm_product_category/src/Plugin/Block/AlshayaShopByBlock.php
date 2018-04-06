<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Block;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Shop by block.
 *
 * @Block(
 *   id = "alshaya_shop_by_block",
 *   admin_label = @Translation("Shop by"),
 * )
 */
class AlshayaShopByBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Array of terms for cache bubbling up.
   *
   * @var array
   */
  protected $cacheTags = [];

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * AlshayaShopByBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, ProductCategoryTree $product_category_tree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->productCategoryTree = $product_category_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('alshaya_acm_product_category.product_category_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the term object from current route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();

    // Set default parent_id 0 to load first level category terms.
    $parent_id = 0;

    // Load category top level menu settings.
    $config = $this->configFactory->get('alshaya_acm_product_category.super_category.settings');
    if ($config->get('status')) {
      // Set the default parent from settings.
      $parent_id = $config->get('default_category_tid');
      // Get the term id from the current path, and display only the related
      // second level child terms.
      if ($parent = $this->productCategoryTree->getCategoryTermRootParent($term)) {
        // Get the top level parent id if parent exists.
        $parent_id = $parent->id();
      }
    }

    // Child terms of given parent term id.
    $term_data = $this->productCategoryTree->getCategoryTreeCached($parent_id);

    // If no data, no need to render the block.
    if (empty($term_data)) {
      return [];
    }

    // Get all parents of the given term.
    if ($term instanceof TermInterface) {
      $parents = $this->productCategoryTree->getCategoryTermParents($term);

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
      '#theme' => 'alshaya_shop_by',
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
    $this->cacheTags[] = ProductCategoryTree::CACHE_TAG;

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
