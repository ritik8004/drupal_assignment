<?php

namespace Drupal\alshaya_main_menu\Plugin\Block;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

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
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCateoryTree;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The transliteration helper.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * AlshayaMainMenuSuperCategoryBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProductCategoryTree $product_category_tree, LanguageManagerInterface $language_manager, TransliterationInterface $transliteration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCateoryTree = $product_category_tree;
    $this->languageManager = $language_manager;
    $this->transliteration = $transliteration;
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
      $container->get('transliteration')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get current lang code.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // Get all the parents of product category.
    $term_data = $this->productCateoryTree->getCategoryRootTerms();

    // If no data, no need to render the block.
    if (empty($term_data)) {
      return [];
    }

    // Add class for all terms.
    foreach ($term_data as &$term_info) {
      // Create a link class based on taxonomy term name.
      $transliterated = $this->transliteration->transliterate($term_info['label'], $langcode, '_');
      $transliterated = Unicode::strtolower($transliterated);
      $term_info['class'] .= ' brand-' . preg_replace('@[^a-z0-9_]+@', '-', $transliterated);
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
            $term_data[$parent->id()]['class'] .= ' active';
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
