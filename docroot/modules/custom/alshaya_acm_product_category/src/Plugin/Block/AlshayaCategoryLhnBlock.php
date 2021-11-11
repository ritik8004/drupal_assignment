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
use Drupal\alshaya_acm_product_category\ProductCategoryHelper;

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
   * Product Category Helper.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryHelper
   */
  protected $productCategoryHelper;

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
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryHelper $productCategoryHelper
   *   Product category Helper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              ProductCategoryTree $product_category_tree,
                              LanguageManagerInterface $language_manager,
                              RouteMatchInterface $route_match,
                              ModuleHandlerInterface $module_handler,
                              ProductCategoryHelper $productCategoryHelper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->productCategoryTree = $product_category_tree;
    $this->languageManager = $language_manager;
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
    $this->productCategoryHelper = $productCategoryHelper;
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
      $container->get('module_handler'),
      $container->get('alshaya_acm_product_category.helper'),
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

    $build = $this->productCategoryHelper->productCategoryBuild($this->getBaseId(), $term, $langcode);

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
