<?php

namespace Drupal\alshaya_seo_transac;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\simple_sitemap\Simplesitemap;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AlshayaSitemapManager.
 *
 * @package Drupal\alshaya_seo_transac
 */
class AlshayaSitemapManager {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Simple sitemap generator.
   *
   * @var \Drupal\simple_sitemap\Simplesitemap
   */
  protected $generator;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The product category.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategory;

  /**
   * AlshayaSitemapManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity manager.
   * @param \Drupal\simple_sitemap\Simplesitemap $generator
   *   Simple sitemap generator.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category
   *   Product category.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager,
                              Simplesitemap $generator,
                              ConfigFactoryInterface $config_factory,
                              ProductCategoryTree $product_category) {
    $this->entityManager = $entity_manager;
    $this->generator = $generator;
    $this->configFactory = $config_factory;
    $this->productCategory = $product_category;
  }

  /**
   * A helper function to remove sitemap variant.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The term object.
   */
  public function removeSitemapVariant(EntityInterface $entity) {
    $variant_name = $this->sitemapVariantName($entity->id());
    $variants = $this->getAllVariants();

    if (in_array($variant_name, $variants)) {
      $this->generator->getSitemapManager()->removeSitemapVariants($variant_name);
    }
  }

  /**
   * A helper function to add sitemap variant.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The term object.
   */
  public function addSitemapVariant(EntityInterface $entity) {
    $variant_name = $this->sitemapVariantName($entity->id());
    $variants = $this->getAllVariants();

    if (!in_array($variant_name, $variants)) {
      $settings = ['type' => 'default_hreflang', 'label' => $entity->getName()];
      $this->generator->getSitemapManager()->addSitemapVariant($this->getVariantName($entity->getName()), $settings);
    }
  }

  /**
   * A helper function to return variant name.
   *
   * @param int $term_id
   *   The term id.
   */
  public function sitemapVariantName(int $term_id) {
    $variant_name = '';
    $term = $this->entityManager->getStorage('taxonomy_term')->load($term_id);
    $term = $this->productCategory->getL1Category($term);

    if ($term->get('field_commerce_status')->getString()) {
      $variant_name = $this->getVariantName($term->getName());
    }

    return $variant_name;
  }

  /**
   * A helper function to update and remove variant based indexing.
   *
   * @param int $entity_id
   *   The entity id.
   * @param string $entity_type
   *   The entity type.
   */
  public function acqProductOperation(int $entity_id, string $entity_type) {
    $variants_list = [];
    $entity = $this->entityManager->getStorage($entity_type)->load($entity_id);
    if ($entity_type == 'node') {
      $category = $entity->get('field_category')->getValue();

      if (!empty($category) && isset($category)) {
        foreach ($category as $tid) {
          $variants_list[] = $this->sitemapVariantName($tid['target_id']);
        }
      }
    }

    $this->resetEntityVariantSettings($variants_list, $entity_type, $entity_id);
  }

  /**
   * A helper function to update and remove variant based indexing.
   *
   * @param int $entity_id
   *   The entity id.
   * @param string $entity_type
   *   The entity type.
   * @param bool $entity_status
   *   The entity status.
   */
  public function acqProductCategoryOperation(int $entity_id, string $entity_type, $entity_status) {
    $variants_list = [];

    if ($entity_status) {
      $variants_list[] = $this->sitemapVariantName($entity_id);
    }

    $this->resetEntityVariantSettings($variants_list, $entity_type, $entity_id);
  }

  /**
   * A helper function to reset variants for an entity.
   *
   * @param array $active_variants
   *   The active variants for an entity.
   * @param string $entity_type_id
   *   An entity type id.
   * @param string $entity_id
   *   An entity id.
   */
  public function resetEntityVariantSettings(array $active_variants, $entity_type_id, $entity_id) {
    $variants = $this->getAllVariants();

    if (!empty($active_variants)) {
      // Set index for active variants.
      $this->generator->setVariants($active_variants);
      $this->generator->setEntityInstanceSettings($entity_type_id, $entity_id, ['index' => 1]);

      // Unset index for inactive variants.
      $inactive_variants = array_diff($variants, $active_variants);
      $this->generator->setVariants($inactive_variants);
      $this->generator->setEntityInstanceSettings($entity_type_id, $entity_id, ['index' => 0]);
    }
    // Unset index if entity status is disabled.
    else {
      $this->generator->setVariants($variants);
      $this->generator->setEntityInstanceSettings($entity_type_id, $entity_id, ['index' => 0]);
    }
  }

  /**
   * A helper function to enable variants for entity types.
   */
  public function enableEntityTypeVariants() {
    // Get variants.
    $variants = $this->getAllVariants();
    $entity_types = ['taxonomy_term' => 'acq_product_category', 'node' => 'acq_product'];

    foreach ($entity_types as $entity_type_id => $bundle_types) {
      foreach ($variants as $variant) {
        $this->generator
          ->setVariants([$variant])
          ->setBundleSettings($entity_type_id, $bundle_types, ['index' => TRUE]);
      }
      // Disable default variant for product and product category.
      $this->generator
        ->setVariants(['default'])
        ->setBundleSettings($entity_type_id, $bundle_types, ['index' => 0]);
    }
  }

  /**
   * A helper function to get variant name based on parent term name.
   *
   * @param string $term_name
   *   The parent term name.
   */
  public function getVariantName($term_name) {
    // Replaces all spaces with hyphens.
    $term_name = str_replace(' ', '-', strtolower(trim($term_name)));

    // Removes special chars.
    $term_name = preg_replace('/[^A-Za-z0-9\-]/', '', $term_name);

    // Replaces multiple hyphens with single one.
    return preg_replace('/-+/', '-', $term_name);
  }

  /**
   * Get list of variants.
   */
  public function getAllVariants() {
    $variants = array_keys($this->generator->getSitemapManager()->getSitemapVariants());
    return array_diff($variants, ['default']);
  }

  /**
   * Get the parent depth.
   */
  public function variantWithParentDepth() {
    $super_category_status = $this->configFactory->get('alshaya_super_category.settings')->get('status');
    $term_data = $this->productCategory->getCategoryTreeCached();

    if ($super_category_status) {
      if (!empty($term_data)) {
        foreach ($term_data as $parent) {
          $this->addVariantWithParentDepth($parent['child']);
        }
      }
    }
    else {
      $this->addVariantWithParentDepth($term_data);
    }
  }

  /**
   * Create variant as per parent depth.
   */
  public function addVariantWithParentDepth($term_data) {
    if (!empty($term_data)) {
      foreach ($term_data as $parent) {
        $settings = ['type' => 'default_hreflang', 'label' => $parent['label']];
        $variant_name = $this->getVariantName($parent['label']);
        $this->generator->getSitemapManager()->addSitemapVariant($variant_name, $settings);
      }
    }
  }

}
