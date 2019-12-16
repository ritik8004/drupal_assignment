<?php

namespace Drupal\alshaya_seo_transac;

use Drupal\simple_sitemap\Simplesitemap;
use Drupal\taxonomy\TermInterface;
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
   * A helper function to remove variant.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The term object.
   */
  public function removeSitemapVariant(TermInterface $term) {
    $variant_name = $this->sitemapVariantName($term->id(), FALSE);
    $sitemap_manager = $this->generator->getSitemapManager();
    $variants = array_keys($sitemap_manager->getSitemapVariants());

    if (in_array($variant_name, $variants)) {
      $variants = $sitemap_manager->removeSitemapVariants($variant_name);
    }
  }

  /**
   * A helper function to add variant.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The term object.
   */
  public function addSitemapVariant(TermInterface $term) {
    $variant_name = $this->sitemapVariantName($term->id(), FALSE);

    $sitemap_manager = $this->generator->getSitemapManager();
    $variants = array_keys($sitemap_manager->getSitemapVariants());

    if (!in_array($variant_name, $variants)) {
      $settings = ['type' => 'default_hreflang', 'label' => $term->getName()];
      $sitemap_manager->addSitemapVariant($variant_name, $settings);
    }
  }

  /**
   * A helper function to return variant name.
   *
   * @param int $term_id
   *   The term id.
   * @param bool $is_parent
   *   To check parent term.
   */
  public function sitemapVariantName($term_id, $is_parent = TRUE) {
    $variant_name = '';
    if ($is_parent) {
      $ancestors = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($term_id);
      $term_id = reset(array_reverse(array_keys($ancestors)));
    }

    if ($term_id) {
      $term = $this->entityManager->getStorage('taxonomy_term')->load($term_id);
      $variant_name = 'shop-' . str_replace(' ', '-', strtolower(trim($term->getName())));
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

    if ($entity_type == 'taxonomy_term' && $entity_status) {
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
    $variants = array_keys($this->generator->getSitemapManager()->getSitemapVariants());

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

}
