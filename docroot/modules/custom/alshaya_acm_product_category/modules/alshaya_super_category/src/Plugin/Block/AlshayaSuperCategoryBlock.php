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
use Drupal\metatag\MetatagManagerInterface;
use Drupal\Core\Utility\Token;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * Meta tag manager.
   *
   * @var \Drupal\metatag\MetatagManagerInterface
   */
  protected $metaTagManager;

  /**
   * Token manager.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $tokenManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\metatag\MetatagManagerInterface $metatag_manager
   *   Meta tag manager.
   * @param \Drupal\Core\Utility\Token $token_manager
   *   Token manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProductCategoryTree $product_category_tree, LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory, MetatagManagerInterface $metatag_manager, Token $token_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCategoryTree = $product_category_tree;
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->metaTagManager = $metatag_manager;
    $this->tokenManager = $token_manager;
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
      $container->get('alshaya_acm_product_category.product_category_tree'),
      $container->get('language_manager'),
      $container->get('config.factory'),
      $container->get('metatag.manager'),
      $container->get('token'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get all the parents of product category.
    $term_data = $this->productCategoryTree->getCategoryRootTerms();

    if (empty($term_data)) {
      return [];
    }

    // Get all child terms for the given parent.
    $term_data_en = $this->productCategoryTree->getCategoryTree('en', 0, FALSE, FALSE);

    $current_language = $this->languageManager->getCurrentLanguage()->getId();

    // Add class for all terms.
    foreach ($term_data as $term_id => &$term_info) {
      $term_object = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);

      // We need to do this or meta tag always gets/renders value from default
      // english term.
      if ($term_object->hasTranslation($current_language)) {
        $term_object = $term_object->getTranslation($current_language);
      }

      if ($term_object instanceof TermInterface) {
        if (!empty($meta_tags = $this->metaTagManager->tagsFromEntityWithDefaults($term_object))) {
          $term_info['meta_title'] = $this->tokenManager->replace(
            $meta_tags['title'],
            ['term' => $term_object]
          );
        }
      }

      $term_info_en = ($current_language !== 'en') ? $term_data_en[$term_id] : $term_info;
      $term_info['class'] = ' brand-' . Html::cleanCssIdentifier(Unicode::strtolower($term_info_en['label']));
    }

    // Set the default parent from settings.
    $parent_id = alshaya_super_category_get_default_term();

    // Set default category link to redirect to home page.
    // Default category is set to active, while we are on home page.
    if (isset($term_data[$parent_id])) {
      $term_data[$parent_id]['path'] = Url::fromRoute('<front>')->toString();
    }

    // Get current term from route.
    $term = $this->productCategoryTree->getCategoryTermRequired();
    if (!empty($term) && isset($term_data[$term['id']])) {
      $term_data[$term['id']]['class'] .= ' active';
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
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path', 'url.query_args:brand']);
  }

}
