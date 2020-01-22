<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Block;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides category LHN block.
 *
 * @Block(
 *   id = "alshaya_category_lhn_block",
 *   admin_label = @Translation("Category LHN Block"),
 * )
 */
class AlshayaCategoryLhnBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Config to enable/disable the lhn category tree.
   */
  const ENABLE_DISABLE_CONFIG = 'alshaya_acm_product_category.settings';

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              ProductCategoryTree $product_category_tree,
                              LanguageManagerInterface $language_manager,
                              RouteMatchInterface $route_match,
                              ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->productCategoryTree = $product_category_tree;
    $this->languageManager = $language_manager;
    $this->routeMatch = $route_match;
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
      $container->get('language_manager'),
      $container->get('current_route_match'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    static $build = NULL;

    // Adding static cache as this block is invoked in
    // alshaya_white_label_preprocess_page().
    if (isset($build)) {
      return $build;
    }

    $build = [];

    // Get the term object from current route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();
    if (!$term instanceof TermInterface) {
      return $build;
    }

    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $parent_id = 0;

    $context = [
      'block' => $this->getBaseId(),
      'term' => $term,
      'depth_offset' => 0,
    ];
    // Invoke the alter hook to allow all modules to update parent_id.
    $this->moduleHandler->alter('product_category_parent', $parent_id, $context);

    // Get the term tree.
    $term_data = $this->productCategoryTree->getCategoryTreeWithIncludeInMenu($langcode, $parent_id);

    // If no data, no need to render the block.
    if (empty($term_data)) {
      return $build;
    }

    $lhn_tree = [];

    // Get all parents of the given term.
    $parents = $this->productCategoryTree->getCategoryTermParents($term);

    // If parent exists for the current term. Here doing
    // `$context['depth_offset'] + 1` as $parents array contains the current
    // term as well.
    if ((count($parents) - ($context['depth_offset'] + 1)) > 0) {
      // Get root parent term.
      /* @var \Drupal\taxonomy\Entity\Term $root_parent_term*/
      $root_parent_term = array_pop($parents);

      if (!empty($context['depth_offset'])) {
        $root_parent_term = array_pop($parents);
      }

      $lhn_tree = $term_data[$root_parent_term->id()]['child'];
    }
    elseif (!empty($context['depth_offset'])) {
      $lhn_tree = count($parents) == 1 ? $term_data : $term_data[key($parents)]['child'];
      if (count($parents) == 1) {
        $context['depth_offset'] = 2;
      }
    }
    else {
      $lhn_tree = count($parents) == 1 ? $term_data[key($parents)]['child'] : [];
    }

    $lhn_tree = array_filter($lhn_tree, function ($tree_term) {
      return $tree_term['lhn'];
    });

    if (empty($lhn_tree)) {
      return $build;
    }

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
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $term = $this->routeMatch->getParameter('taxonomy_term');
    $department_pages = alshaya_advanced_page_get_pages();
    $config = $this->configFactory->get(self::ENABLE_DISABLE_CONFIG);
    // Not allow if department page exists for category or lhn is disabled.
    return AccessResult::allowedif(empty($department_pages[$term->id()]) && $config->get('enable_lhn_tree'));
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Get cache tags of enable/disable lhn block config.
    $config_cache_tags = $this->configFactory
      ->get(self::ENABLE_DISABLE_CONFIG)
      ->getCacheTags();

    $cache_tags = array_merge($config_cache_tags, [ProductCategoryTree::CACHE_TAG]);

    return Cache::mergeTags(
      parent::getCacheTags(),
      $cache_tags
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}
