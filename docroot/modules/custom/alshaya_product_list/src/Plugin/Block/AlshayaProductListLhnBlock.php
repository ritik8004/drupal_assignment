<?php

namespace Drupal\alshaya_product_list\Plugin\Block;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides Product List LHN block.
 *
 * @Block(
 *   id = "alshaya_product_list_lhn_block",
 *   admin_label = @Translation("Product List LHN Block"),
 * )
 */
class AlshayaProductListLhnBlock extends BlockBase implements ContainerFactoryPluginInterface {

  const CONTENT_TYPE = 'product_list';
  const VOCAB_ID = 'acq_product_category';

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
   * Entity Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              ProductCategoryTree $product_category_tree,
                              LanguageManagerInterface $language_manager,
                              RouteMatchInterface $route_match,
                              ModuleHandlerInterface $module_handler,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->productCategoryTree = $product_category_tree;
    $this->languageManager = $language_manager;
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $current_route_name = $this->routeMatch->getRouteName();
    if ($current_route_name === 'entity.node.canonical') {
      // Get the node object from current route.
      $node = $this->routeMatch->getParameter('node');
      $bundle = $node->bundle();
      if ($bundle === self::CONTENT_TYPE) {
        if ($node->get('field_show_in_lhn_options_list')) {
          $product_list_lhn_options_list_value = $node->get('field_show_in_lhn_options_list')[0];
          if ($product_list_lhn_options_list_value === NULL) {
            $product_list_lhn_value = 'Same as LHN';
          }
          else {
            $product_list_lhn_value = $node->get('field_show_in_lhn_options_list')->getValue()[0]['value'];
          }
          $vocab_list = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
            'vid' => self::VOCAB_ID,
            'langcode' => $langcode,
          ]);
          if (empty($vocab_list)) {
            return [];
          }
          switch ($product_list_lhn_value) {
            case "Same as LHN":
              foreach ($vocab_list as $term) {
                if ($term->get('field_show_in_lhn')) {
                  $term_show_lhn_value = $term->get('field_show_in_lhn')->getValue()[0]['value'];
                  if ($term_show_lhn_value === '1') {
                    $build = $this->productListLhnBuild($term, $langcode);
                  }
                }
              }
              break;

            case "Yes":
              foreach ($vocab_list as $term) {
                $build = $this->productListLhnBuild($term, $langcode);
              }
              break;
          }
        }
      }
      return $build;
    }
  }

  /**
   * Function to build the LHN.
   */
  protected function productListLhnBuild($term, $langcode) {
    static $build = NULL;

    // Adding static cache as this block is invoked in
    // alshaya_white_label_preprocess_page().
    if (isset($build)) {
      return $build;
    }
    $build = [];
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
      return $build;
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
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_merge_tags = $this->routeMatch->getParameter('node')->getCacheTags();
    $cache_tags = array_merge($cache_merge_tags, [ProductCategoryTree::CACHE_TAG]);

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
