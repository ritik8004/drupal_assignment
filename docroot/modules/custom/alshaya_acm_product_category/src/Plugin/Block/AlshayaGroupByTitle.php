<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Block;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Provides Sub Category Title Block.
 *
 * @Block(
 *   id = "alshaya_sub_category_title_block",
 *   admin_label = @Translation("Sub Category Title Block (Panty Guide)"),
 * )
 */
class AlshayaGroupByTitle extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaGroupByTitle constructor.
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
   *   Language Manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ProductCategoryTree $product_category_tree,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCategoryTree = $product_category_tree;
    $this->languageManager = $language_manager;
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
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the term object from current route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();
    $current_language = $this->languageManager->getCurrentLanguage()->getId();

    $title = NULL;
    $description = NULL;
    if ($term instanceof TermInterface && $term->get('field_group_by_sub_categories')->getString()) {
      // Get all selected subcategories to be displayed on PLP.
      if ($term->hasTranslation($current_language)) {
        $term = $term->getTranslation($current_language);
      }

      $title = $term->label();
      $description = !empty($term->get('description')->getValue())
        ? $term->get('description')->getValue()[0]['value']
        : NULL;

      return [
        '#theme' => 'alshaya_sub_category_title',
        '#title' => $title,
        '#description' => $description,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = parent::getCacheTags();

    $term = $this->productCategoryTree->getCategoryTermFromRoute();

    if ($term instanceof TermInterface && $term->get('field_group_by_sub_categories')->getString()) {
      // Add current term tags always.
      $tags = Cache::mergeTags($tags, $term->getCacheTags());
    }

    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Get the term object from current route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();

    if ($term instanceof TermInterface && $term->get('field_group_by_sub_categories')) {
      if ($term->get('field_group_by_sub_categories')->getString()) {
        $cachetags = $this->getCacheTags();

        // Allowed if group by sub categories is enabled.
        return AccessResult::allowed()->addCacheTags($cachetags);
      }

      // Denied if group by sub categories is not enabled.
      // We still add current term cache tags for access check.
      return AccessResult::forbidden()->addCacheTags($term->getCacheTags());
    }
    return AccessResult::forbidden();
  }

}
