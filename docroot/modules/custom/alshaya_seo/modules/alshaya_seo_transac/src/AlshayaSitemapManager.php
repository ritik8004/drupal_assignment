<?php

namespace Drupal\alshaya_seo_transac;

use Drupal\simple_sitemap\Simplesitemap;
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
   * AlshayaSitemapManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity manager.
   * @param \Drupal\simple_sitemap\Simplesitemap $generator
   *   Simple sitemap generator.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager,
                              Simplesitemap $generator) {
    $this->entityManager = $entity_manager;
    $this->generator = $generator;
  }

  /**
   * A helper function to remove sitemap variant.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The term object.
   */
  public function removeSitemapVariant(EntityInterface $entity) {
    $variant_name = $this->sitemapVariantName($entity->id(), FALSE);
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
    $variant_name = $this->sitemapVariantName($entity->id(), FALSE);
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
   * @param bool $is_child
   *   To check term status.
   */
  public function sitemapVariantName(int $term_id, $is_child = TRUE) {
    $variant_name = '';
    if ($is_child) {
      $ancestors = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($term_id);
      $term_id = reset(array_reverse(array_keys($ancestors)));
    }

    if (!empty($term_id)) {
      $term = $this->entityManager->getStorage('taxonomy_term')->load($term_id);
      if ($term->get('field_commerce_status')->getString()) {
        $variant_name = $this->getVariantName($term->getName());
      }
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
      $this->enableEntityTypeVariants(['taxonomy_term' => 'acq_product_category', 'node' => 'acq_product']);
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
   *
   * @param array $entity_types
   *   The entity types.
   */
  public function enableEntityTypeVariants(array $entity_types) {
    // Skip default variant.
    $variants = array_diff($this->getAllVariants(), ['default']);

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
    return str_replace(' ', '-', strtolower(trim($term_name)));
  }

  /**
   * Get list of variants.
   */
  public function getAllVariants() {
    return array_keys($this->generator->getSitemapManager()->getSitemapVariants());
  }

}
