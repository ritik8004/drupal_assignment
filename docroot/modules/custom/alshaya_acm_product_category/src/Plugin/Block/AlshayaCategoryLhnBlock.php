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
   * Cache id for LHN category tree.
   */
  const CACHE_ID = 'product_category_lhn_tree';

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              ProductCategoryTree $product_category_tree,
                              LanguageManagerInterface $language_manager,
                              RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->productCategoryTree = $product_category_tree;
    $this->languageManager = $language_manager;
    $this->routeMatch = $route_match;
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
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the term object from current route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();
    if (!$term instanceof TermInterface) {
      return [];
    }

    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // Get the term tree.
    $term_data = $this->productCategoryTree->getCategoryTreeWithIncludeInMenu($langcode);

    // If no data, no need to render the block.
    if (empty($term_data)) {
      return [];
    }

    $lhn_tree = [];

    // Get all parents of the given term.
    if ($term instanceof TermInterface) {
      $parents = $this->productCategoryTree->getCategoryTermParents($term);

      // If parent exists for the current term. Here doing `-1` as $parents
      // array contains the current term as well.
      if ((count($parents) - 1) > 0) {
        // Get root parent term.
        /* @var \Drupal\taxonomy\Entity\Term $root_parent_term*/
        $root_parent_term = array_pop($parents);
        $lhn_tree = $term_data[$root_parent_term->id()]['child'];
      }
      else {
        // If no parent, it's a L1 term.
        $lhn_tree = $term_data[$term->id()]['child'];
      }
    }

    return [
      '#theme' => 'alshaya_lhn_tree',
      '#lhn_cat_tree' => $lhn_tree,
      '#current_term' => $term->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    $term = $this->routeMatch->getParameter('taxonomy_term');
    $department_pages = alshaya_advanced_page_get_pages();
    // Not allow if department page exists for category.
    return AccessResult::allowedif(empty($department_pages[$term->id()]));
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(
      parent::getCacheTags(),
      [ProductCategoryTree::CACHE_TAG]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}
