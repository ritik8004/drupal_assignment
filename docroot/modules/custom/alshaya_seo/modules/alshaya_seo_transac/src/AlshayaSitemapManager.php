<?php

namespace Drupal\alshaya_seo_transac;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\simple_sitemap\Simplesitemap;
use Drupal\taxonomy\TermInterface;
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
  private function getAllVariants() {
    $variants = array_keys($this->generator->getSitemapManager()->getSitemapVariants());
    return array_diff($variants, ['default']);
  }

  /**
   * Get the parent depth.
   */
  public function configureVariants() {
    $term_data = $this->productCategory->getCategoryTreeCached();

    if (empty($term_data)) {
      // Throw some error? Delete existing extra variants?
      return;
    }

    foreach ($term_data as $l1) {
      $this->createVariantForL1($l1);
    }

    // @TODO: Delete obsolete variants for the case where term name changed
    // may be?
  }

  /**
   * Create variant as per parent depth.
   */
  private function createVariantForL1($term_data) {
    /** @var \Drupal\taxonomy\Entity\Term $term */
    $term = $this->entityManager->getStorage('taxonomy_term')->load($term_data['id']);
    if ($term->language()->getId() != 'en' && $term->hasTranslation('en')) {
      $term = $term->getTranslation('en');
    }

    if ($this->productCategory->isCategoryL1($term)) {
      $this->addSitemapVariant($term);
    }
    else {
      foreach ($term_data['child'] as $l1) {
        $this->createVariantForL1($l1);
      }
    }
  }

  /**
   * A helper function to add sitemap variant.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The term object.
   */
  private function addSitemapVariant(TermInterface $term) {
    $variant_name = $this->getVariantName($term->toUrl()->toString());
    $variants = $this->getAllVariants();

    if (!in_array($variant_name, $variants)) {
      $settings = [
        'type' => 'alshaya_hreflang',
        'label' => $term->id(),
        'weight' => $term->getWeight(),
      ];
      $this->generator->getSitemapManager()->addSitemapVariant($this->getVariantName($term->toUrl()->toString()), $settings);
    }
  }

}
