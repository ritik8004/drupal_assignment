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
    $cache_key = implode(':', [
      'product_media',
      (int) $check_parent_child,
      $context,
    ]);

    $cache = $this->productCacheManager->get($sku, $cache_key);

    if (is_array($cache)) {
      return $cache;
    }

    $data = $this->getGalleryMedia($sku);

    foreach ($data['media_items']['images'] ?? [] as $key => $item) {
      if (empty($item['label'])) {
        $data['media_items']['images'][$key]['label'] = (string) $sku->label();
      }
    }

    $this->productCacheManager->set($sku, $cache_key, $data);

    return $data;
  }

  /**
   * Utility function to return all media files for a SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param bool $check_parent_child
   *   Check parent or child SKUs.
   *
   * @return array
   *   Array of media files.
   */
  public function getGalleryMedia(SKUInterface $sku, $check_parent_child = FALSE) {
    $static = &drupal_static(__FUNCTION__, []);

    $static_id = implode(':', [
      $sku->getSku(),
      $sku->language()->getId(),
      (int) $check_parent_child,
    ]);

    if (isset($static[$static_id])) {
      return $static[$static_id];
    }

    $media = unserialize($sku->get('media')->getString());

    foreach ($media ?? [] as $index => $media_item) {
      if (!isset($media_item['media_type'])) {
        continue;
      }

      // Check for roles only if available.
      if (!isset($media_item['roles'])) {
        continue;
      }

      // If the image has base image role, we show it even if it is swatch
      // or thumbnail.
      if (in_array(self::BASE_IMAGE_ROLE, $media_item['roles'])) {
        continue;
      }

      // Loop through all the roles we need to hide from gallery.
      foreach ($this->getImageRolesToHide() as $role_to_hide) {
        if (in_array($role_to_hide, $media_item['roles'])) {
          unset($media[$index]);
          break;
        }
      }
    }

    // Avoid notices and warnings in local.
    if ($check_parent_child && empty($media)) {
      if ($sku->bundle() == 'simple') {
        /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
        $plugin = $sku->getPluginInstance();
        $parent = $plugin->getParentSku($sku);

        // Check if there is parent SKU available, use media files of parent.
        if ($parent instanceof SKUInterface) {
          return $this->getGalleryMedia($parent);
        }
      }
      elseif ($sku->bundle() == 'configurable') {
        $child = $this->getFirstChildWithMedia($sku);

        // Check if there is child SKU available, use media files of child.
        if ($child instanceof SKUInterface) {
          return $this->getGalleryMedia($child, FALSE);
        }
      }
    }

    $return = [];
    $media = !empty($media) ? array_filter($media) : [];
    foreach ($media as $media_item) {
      if ($media_item['media_type'] == 'image') {
        $media_item['drupal_uri'] = $media_item['file'];
        $return['media_items']['images'][] = $media_item;
      }
      elseif (!empty($media_item['video_url']) || $media_item['media_type'] === 'video') {
        $return['media_items']['videos'][] = $media_item;
      }
    }

    // For simple children we need to add images from parent
    // if configured to do so.
    if ($sku->bundle() === 'simple' && !$check_parent_child && $this->addParentImagesInChild()) {
      /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
      $plugin = $sku->getPluginInstance();
      $parent = $plugin->getParentSku($sku);

      if ($parent instanceof SKUInterface) {
        $parent_media = $this->getGalleryMedia($parent, FALSE);
        $return = array_merge_recursive($return, $parent_media);
      }
    }

    $static[$static_id] = $return;
    return $return;
  }

}
