<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Block;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Sub Category Title Block.
 *
 * @Block(
 *   id = "alshaya_group_by_sub_category_page_title",
 *   admin_label = @Translation("Sub Category page Title (Panty Guide)"),
 * )
 */
class AlshayaGroupBySubCategoryPageTitle extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * AlshayaGroupBySubCategoryPageTitle constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ProductCategoryTree $product_category_tree,
    EntityRepositoryInterface $entity_repository
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCategoryTree = $product_category_tree;
    $this->entityRepository = $entity_repository;
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
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the term object from current route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();

    $title = NULL;
    $description = NULL;
    if ($term instanceof TermInterface && $term->get('field_group_by_sub_categories')->getString()) {
      // Get all selected subcategories to be displayed on PLP.
      $term = $this->entityRepository->getTranslationFromContext($term);

      $title = !empty($term->get('field_plp_group_category_title')->getValue())
        ? $term->get('field_plp_group_category_title')->getValue()[0]['value']
        : $term->label();

      $description = !empty($term->get('field_plp_group_category_desc')->getValue())
        ? $term->get('field_plp_group_category_desc')->getValue()[0]['value']
        : (!empty($term->get('description')->getValue())
          ? $term->get('description')->getValue()[0]['value']
          : NULL);

      return [
        '#theme' => 'alshaya_group_by_sub_category_page_title',
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

    if ($term instanceof TermInterface && $term->hasField('field_group_by_sub_categories')) {
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

    if ($term instanceof TermInterface && $term->hasField('field_group_by_sub_categories')) {
      $cachetags = $this->getCacheTags();

      // Allowed if group by sub categories is enabled.
      return AccessResult::allowedIf($term->get('field_group_by_sub_categories')->getString())->addCacheTags($cachetags);
    }
    return AccessResult::forbidden();
  }

}
