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
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileInterface;

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
   * File Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ProductCategoryTree $product_category_tree,
    EntityRepositoryInterface $entity_repository,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCategoryTree = $product_category_tree;
    $this->entityRepository = $entity_repository;
    $this->fileStorage = $entity_type_manager->getStorage('file');
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
      $container->get('entity.repository'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the term object from current route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();

    if ($term instanceof TermInterface && $term->get('field_group_by_sub_categories')->getString()) {
      // Get all selected subcategories to be displayed on PLP.
      $term = $this->entityRepository->getTranslationFromContext($term);
      $data = [];
      $data['tid'] = $term->id();
      $data['title'] = !empty($term->get('field_plp_group_category_title')->getValue())
        ? $term->get('field_plp_group_category_title')->getValue()[0]['value']
        : $term->label();

      $data['description'] = !empty($term->get('field_plp_group_category_desc')->getValue())
        ? $term->get('field_plp_group_category_desc')->getValue()[0]['value']
        : (!empty($term->get('description')->getValue())
          ? $term->get('description')->getValue()[0]['value']
          : NULL);

      if ($term->get('field_plp_group_category_img')->first()) {
        $file_value = $term->get('field_plp_group_category_img')->first()->getValue();
        $image = $this->fileStorage->load($file_value['target_id']);
        if ($image instanceof FileInterface) {
          $data['image']['url'] = file_url_transform_relative(file_create_url($image->getFileUri()));
          $data['image']['alt'] = $file_value['alt'];
        }
      }

      return [
        '#theme' => 'alshaya_group_by_sub_category_page_title',
        '#group_category_header' => $data,
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
