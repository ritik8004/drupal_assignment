<?php

namespace Drupal\alshaya_seo_transac;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\simple_sitemap\Simplesitemap;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class Alshaya Sitemap Manager.
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
   * The database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

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
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection service.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager,
                              Simplesitemap $generator,
                              ConfigFactoryInterface $config_factory,
                              ProductCategoryTree $product_category,
                              Connection $database) {
    $this->entityManager = $entity_manager;
    $this->generator = $generator;
    $this->configFactory = $config_factory;
    $this->productCategory = $product_category;
    $this->database = $database;
  }

  /**
   * A helper function to remove sitemap variant.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The term object.
   */
  public function removeSitemapVariant(EntityInterface $entity) {
    $variant_name = $this->getVariantName($entity->toUrl()->toString());
    $variants = $this->getAllVariants();

    if (in_array($variant_name, $variants)) {
      $this->generator->getSitemapManager()->removeSitemapVariants($variant_name);
    }
  }

  /**
   * A helper function to get variant name based on parent term name.
   *
   * @param string $path
   *   The parent term path.
   */
  private function getVariantName($path) {
    // Remove langcode from path and use it with underscores.
    $path = array_filter(explode('/', $path));
    array_shift($path);
    return implode('_', $path);
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
  public function configureVariants() {
    $term_data = $this->productCategory->getCategoryTreeCached();

    $existing_variants = $this->getAllVariants();

    $variants = [];
    if (!empty($term_data)) {
      foreach ($term_data as $l1) {
        $term_variants = $this->createVariantForL1($l1);
        $variants = array_merge($variants, $term_variants);
      }
    }

    // Remove the variants which are no longer available.
    $obsolete_variants = array_diff($existing_variants, $variants);
    foreach ($obsolete_variants as $variant) {
      $this->generator->getSitemapManager()->removeSitemapVariants($variant);
    }
  }

  /**
   * Create variant as per parent depth.
   *
   * @return array
   *   Array of variant names created for the L1.
   */
  private function createVariantForL1($term_data) {
    $variants = [];

    /** @var \Drupal\taxonomy\Entity\Term $term */
    $term = $this->entityManager->getStorage('taxonomy_term')->load($term_data['id']);
    if ($term->language()->getId() != 'en' && $term->hasTranslation('en')) {
      $term = $term->getTranslation('en');
    }

    if ($this->productCategory->isCategoryL1($term)) {
      $variants[] = $this->addSitemapVariant($term);
    }
    else {
      foreach ($term_data['child'] as $l1) {
        $child_variants = $this->createVariantForL1($l1);
        $variants = array_merge($variants, $child_variants);
      }
    }

    return $variants;
  }

  /**
   * A helper function to add sitemap variant.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The term object.
   *
   * @return string
   *   Variant name.
   */
  private function addSitemapVariant(TermInterface $term) {
    $variant_name = $this->getVariantName($term->toUrl()->toString());

    $settings = [
      'type' => 'alshaya_hreflang',
      'label' => $term->id(),
      'weight' => $term->getWeight(),
    ];
    // If variant is already present, then the following will update the list of
    // variants with the updated value in $settings. It will not create a
    // duplicate variant.
    $this->generator->getSitemapManager()->addSitemapVariant($this->getVariantName($term->toUrl()->toString()), $settings);

    return $variant_name;
  }

  /**
   * Returns the sitemap publishing status of sitemaps.
   *
   * @return array
   *   Array of sitemap statuses keyed by variant name.
   *   Status values:
   *   0: Instance is unpublished
   *   1: Instance is published
   *   2: Instance is published but is being regenerated
   */
  public function fetchSitemapInstanceStatuses() {
    $query = $this->database->select('simple_sitemap', 's');
    $query->fields('s', ['type', 'status']);
    $query->groupBy('type');
    $query->groupBy('status');
    $results = $query->execute()->fetchAll();

    $instances = [];
    foreach ($results as $result) {
      $instances[$result->type] = isset($instances[$result->type])
        ? $result->status + 1
        : (int) $result->status;
    }

    $all_variants = $this->getAllVariants();
    foreach ($all_variants as $variant) {
      if (!isset($instances[$variant])) {
        $instances[$variant] = 0;
      }
    }

    return $instances;
  }

}
