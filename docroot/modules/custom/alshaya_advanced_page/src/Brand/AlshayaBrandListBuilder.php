<?php

namespace Drupal\alshaya_advanced_page\Brand;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AlshayaBrandListBuilder.
 */
class AlshayaBrandListBuilder {

  /**
   * Taxonomy used for product brand.
   */
  const BRAND_VID = 'sku_product_option';

  /**
   * Attribute code of the product brand.
   */
  const BRAND_ATTRIBUTE_CODE = 'product_brand';

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * AlshayaBrandListBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Load all product brand terms.
   */
  public function loadBrandTerms() {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree(self::BRAND_VID, 0, NULL, TRUE);
    $brand_terms = [];
    if (!empty($terms)) {
      foreach ($terms as $term) {
        // Check attribute_code of terms to add them in brand list.
        if ($term->get('field_sku_attribute_code')->value === self::BRAND_ATTRIBUTE_CODE) {
          $brand_terms[$term->id()] = $term;
        }
      }
    }
    return $brand_terms;
  }

  /**
   * Get brand images for terms.
   */
  public function getBrandImages() {
    $brand_images = [];
    $terms = $this->loadBrandTerms();
    if (!empty($terms)) {
      foreach ($terms as $term) {
        $swatch_image = '';
        // Fetch brand image from the brand list.
        if (isset($term->get('field_attribute_swatch_image')->entity)) {
          $swatch_image = file_create_url($term->get('field_attribute_swatch_image')->entity->getFileUri());
        }
        $brand_images[$term->id()] = $swatch_image;
      }
    }
    return $brand_images;
  }

}
