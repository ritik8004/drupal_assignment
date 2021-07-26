<?php

namespace Drupal\alshaya_pims;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\alshaya_acm_product\Service\ProductCacheManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Class Sku Images Manager Override.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuImagesManagerOverride extends SkuImagesManager {

  /**
   * Sku images manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $imagesManager;

  /**
   * SkuImagesManager constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_image_manager
   *   Sku image manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\acq_sku\ProductInfoHelper $product_info_helper
   *   Product Info Helper.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend object.
   * @param \Drupal\alshaya_acm_product\Service\ProductCacheManager $product_cache_manager
   *   Product Cache Manager.
   */
  public function __construct(SkuImagesManager $sku_image_manager,
                              ModuleHandlerInterface $module_handler,
                              ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager,
                              SkuManager $sku_manager,
                              ProductInfoHelper $product_info_helper,
                              CacheBackendInterface $cache,
                              ProductCacheManager $product_cache_manager) {
    $this->imagesManager = $sku_image_manager;
    parent::__construct($module_handler,
      $config_factory,
      $entity_type_manager,
      $sku_manager,
      $product_info_helper,
      $cache,
      $product_cache_manager
    );
  }

  /**
   * Get product media items.
   *
   * Applies image display rules - which SKU to use for which case.
   *
   * Uses new event dispatcher to all brands to modify media items to display.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context - pdp/search/modal/teaser.
   * @param bool $check_parent_child
   *   Check parent or child SKUs.
   *
   * @return array
   *   Processed media items.
   */
  public function getProductMedia(SKUInterface $sku, string $context, $check_parent_child = TRUE): array {
    // @todo stop image download from pims and set cache for pims media data.
    return unserialize($sku->get('media')->getString());
  }

}
