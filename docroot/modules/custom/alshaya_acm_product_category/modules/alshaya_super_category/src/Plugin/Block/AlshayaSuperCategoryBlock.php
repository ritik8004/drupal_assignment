<?php

namespace Drupal\alshaya_super_category\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Provides alshaya super category menu block.
 *
 * @Block(
 *   id = "alshaya_super_category_menu",
 *   admin_label = @Translation("Alshaya super category menu")
 * )
 */
class AlshayaSuperCategoryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Array of terms for cache bubbling up.
   *
   * @var array
   */
  protected $cacheTags = [];

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
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaSuperCategoryBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProductCategoryTree $product_category_tree, LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCategoryTree = $product_category_tree;
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_acm_product_category.product_category_tree'),
      $container->get('language_manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get current language code.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // Get all the parents of product category.
    $term_data = $this->productCategoryTree->getCategoryRootTerms();
    // If no data, no need to render the block.
    if (empty($term_data)) {
      return [];
    }

    // Load english term data to set the css class based on term name.
    if ($langcode !== 'en') {
      $term_data_en = $this->productCategoryTree->getCategoryRootTerms('en');
    }

    // Add class for all terms.
    foreach ($term_data as $term_id => &$term_info) {
      $term_info_en = ($langcode !== 'en') ? $term_data_en[$term_id] : $term_info;
      // Create a link class based on taxonomy term name.
      $term_info['class'] = ' brand-' . Html::cleanCssIdentifier(Unicode::strtolower($term_info_en['label']));
    }

    // Set the default parent from settings.
    $parent_id = $this->configFactory->get('alshaya_super_category.settings')->get('default_category_tid');

    if (isset($term_data[$parent_id])) {
      $term_data[$parent_id]['path'] = Url::fromRoute('<front>')->toString();
    }

    // Get current term from route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();
    // Get all parents of the given term.
    if ($term instanceof TermInterface) {
      $parent = $this->productCategoryTree->getCategoryTermRootParent($term);
      if ($parent instanceof TermInterface) {
        $parent_id = $parent->id();
      }
    }

    if (isset($term_data[$parent_id])) {
      $term_data[$parent_id]['class'] .= ' active';
    }

    return [
      '#theme' => 'alshaya_super_category_top_level',
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
