<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Block;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

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
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, ProductCategoryTree $product_category_tree, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->productCategoryTree = $product_category_tree;
    $this->moduleHandler = $module_handler;
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
      $container->get('alshaya_acm_product_category.product_category_tree'),
      $container->get('module_handler')
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

    $context = ['block' => $this->getBaseId(), 'term' => $term];
    // Invoke the alter hook to allow all modules to update parent_id.
    $this->moduleHandler->alter('product_category_parent', $parent_id, $context);

    // Child terms of given parent term id.
    $term_data = $this->productCategoryTree->getCategoryTreeCached($parent_id);

    // If no data, no need to render the block.
    if (empty($term_data)) {
      return [];
    }

    // Exclude terms with overridden target link from "shop by" block.
    // As per requirement, we do not want to show these terms in footer.
    $term_data = array_filter($term_data, fn($term) => !in_array('overridden-link', $term['class']));

    // Get all parents of the given term.
    if ($term instanceof TermInterface) {
      $parents = $this->productCategoryTree->getCategoryTermParents($term);

      if (!empty($parents)) {
        /** @var \Drupal\taxonomy\TermInterface $root_parent_term */
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
