<?php

namespace Drupal\alshaya_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

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
   * AlshayaMegaMenuBlock constructor.
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

    $desktop_main_menu_layout = $this->configFactory->get('alshaya_main_menu.settings')->get('desktop_main_menu_layout');

    if ($desktop_main_menu_layout == 'default' || $desktop_main_menu_layout == 'menu_dynamic_display') {
      $columns_tree = $this->getColumnDataMenuAlgo($term_data);
      // Allow other module to alter links.
      $this->moduleHandler->alter('alshaya_main_menu_links', $columns_tree, $parent_id, $context);
    }

    elseif ($desktop_main_menu_layout == 'menu_inline_display') {
      // Allow other module to alter links.
      $this->moduleHandler->alter('alshaya_main_menu_links', $term_data, $parent_id, $context);
    }

    // Get all parents of the given term.
    if ($term instanceof TermInterface) {
      $parents = $this->productCategoryTree->getCategoryTermParents($term);

      if (!empty($parents)) {
        /* @var \Drupal\taxonomy\TermInterface $root_parent_term */
        foreach ($parents as $parent) {
          if (isset($term_data[$parent->id()])) {
            $term_data[$parent->id()]['class'][] = 'active';
          }
        }
      }
    }

    return [
      '#theme' => 'alshaya_main_menu_level1',
      '#term_tree' => $term_data,
      '#column_tree' => $columns_tree ?? NULL,
      '#menu_type' => $desktop_main_menu_layout,
    ];

  }

  /**
   * Column data after menu algo is applied.
   */
  public function getColumnDataMenuAlgo($term_data) {
    $columns_tree = [];
    foreach ($term_data as $l2s) {
      $max_nb_col = (int) $this->configFactory->get('alshaya_main_menu.settings')->get('max_nb_col');
      $ideal_max_col_length = (int) $this->configFactory->get('alshaya_main_menu.settings')->get('ideal_max_col_length');
      do {
        $columns = [];
        $col = 0;
        $col_total = 0;
        $reprocess = FALSE;

        foreach ($l2s['child'] as $l3s) {
          // 2 below means L2 item + one blank line for spacing).
          $l2_cost = 2 + count($l3s['child']);

          // If we are detecting a longer column than the expected size
          // we iterate with new max.
          if ($l2_cost > $ideal_max_col_length) {
            $ideal_max_col_length = $l2_cost;
            $reprocess = TRUE;
            break;
          }

          if ($col_total + $l2_cost > $ideal_max_col_length) {
            $col++;
            $col_total = 0;
          }

          // If we have too many columns we try with more items per column.
          if ($col >= $max_nb_col) {
            $ideal_max_col_length++;
            break;
          }

          $columns[$col][] = $l3s;

          $col_total += $l2_cost;

        }
      } while ($reprocess || $col >= $max_nb_col);
      $columns_tree[$l2s['label']] = [
        'l1_object' => $l2s,
        'columns' => $columns,
      ];
    }
    return $columns_tree;
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
