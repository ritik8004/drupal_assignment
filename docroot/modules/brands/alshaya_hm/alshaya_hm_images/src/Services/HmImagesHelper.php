<?php

namespace Drupal\alshaya_hm_images\Services;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Service\ProductCacheManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Class containing methods dealing with HM images.
 */
class HmImagesHelper {

  /**
   * Constant for default swatch display type.
   */
  public const LP_SWATCH_DEFAULT = 'RGB';

  /**
   * Sku Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Product Cache Manager.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductCacheManager
   */
  protected $productCacheManager;

  /**
   * The Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Term Storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * HmImagesHelper constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   Sku manager service.
   * @param \Drupal\alshaya_acm_product\Service\ProductCacheManager $product_cache_manager
   *   Product cache manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager service.
   * @param \Drupal\Core\Database\Connection $database
   *   Database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    SkuManager $sku_manager,
    ProductCacheManager $product_cache_manager,
    LanguageManagerInterface $language_manager,
    Connection $database,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->skuManager = $sku_manager;
    $this->productCacheManager = $product_cache_manager;
    $this->languageManager = $language_manager;
    $this->database = $database;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
  }

  /**
   * Helper function to get color label & rgb code for SKU.
   *
   * @param int $sku_id
   *   Entity id for the SKU being processed.
   *
   * @return array
   *   Associative array returning color label & code.
   */
  private function getColorAttributesFromSku($sku_id) {
    $current_langcode = $this->languageManager->getCurrentLanguage()->getId();
    $query = $this->database->select('acq_sku_field_data', 'asfd');
    $query->fields('asfd', ['attr_color_label', 'attr_rgb_color']);
    $query->condition('id', $sku_id);
    $query->condition('langcode', $current_langcode);
    return $query->execute()->fetchAssoc();
  }

  /**
   * Helper function to fetch list of color options supported by a parent SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Parent sku.
   *
   * @return array
   *   Array of RGB color values keyed by article_castor_id.
   */
  public function getColorsForSku(SKU $sku) {
    if ($sku->bundle() != 'configurable') {
      return [];
    }

    if ($cache = $this->productCacheManager->get($sku, 'hm_colors_for_sku')) {
      return $cache;
    }

    $combinations = $this->skuManager->getConfigurableCombinations($sku);
    if (empty($combinations)) {
      return [];
    }

    $child_sku_entity = NULL;
    $article_castor_ids = [];
    $child_sku_cache_tag = [];
    foreach ($combinations['attribute_sku']['article_castor_id'] ?? [] as $skus) {
      $child_sku_entity = NULL;
      $color_attributes = [];

      // Use only the first SKU for which we get color attributes.
      foreach ($skus as $child_sku) {
        // Show only for colors for which we have stock.
        $child_sku_entity = SKU::loadFromSku($child_sku);

        if ($child_sku_entity instanceof SKUInterface && $this->skuManager->isProductInStock($child_sku_entity)) {
          $color_attributes = $this->getColorAttributesFromSku($child_sku_entity->id());
          // Get the cache tags of the child sku.
          $child_sku_cache_tag = $child_sku_entity->getCacheTags() ?? [];
          if ($color_attributes) {
            break;
          }
        }
      }

      if ($child_sku_entity instanceof SKUInterface && $color_attributes) {
        $article_castor_ids[$child_sku_entity->id()] = $color_attributes['attr_rgb_color'];
      }
    }

    $this->productCacheManager->set($sku, 'hm_colors_for_sku', $article_castor_ids, $child_sku_cache_tag);

    return $article_castor_ids;
  }

  /**
   * Helper function to fetch swatch type for the sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Sku for which swatch type needs to be fetched.
   *
   * @return string
   *   Swatch type for the sku.
   */
  public function getSkuSwatchType(SKU $sku) {
    $swatch_type = self::LP_SWATCH_DEFAULT;

    $product_node = $this->skuManager->getDisplayNode($sku);

    if (empty($product_node)) {
      return $swatch_type;
    }

    $terms = $product_node->get('field_category')->getValue();
    if (empty($terms)) {
      return $swatch_type;
    }

    foreach ($terms as $value) {
      $term = $this->termStorage->load($value['target_id']);
      if ($term instanceof TermInterface) {
        $field_swatch_type = $term->get('field_swatch_type')->getString();
        if (!empty($field_swatch_type)) {
          return $field_swatch_type;
        }
      }
    }

    return $swatch_type;
  }

}
