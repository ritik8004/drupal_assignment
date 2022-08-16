<?php

namespace Drupal\alshaya_pims;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\alshaya_acm_product\Service\ProductCacheManager;
use Drupal\alshaya_acm_product\SkuImagesHelper;
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
class SkuImagesManagerPims extends SkuImagesManager {
  // Cache key used for product media.
  public const PRODUCT_MEDIA_CACHE_KEY = 'product_media_pims';

  /**
   * Inner service Sku images manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $innerService;

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
   * @param \Drupal\alshaya_acm_product\SkuImagesHelper $sku_images_helper
   *   Sku images helper.
   */
  public function __construct(SkuImagesManager $sku_image_manager,
                              ModuleHandlerInterface $module_handler,
                              ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager,
                              SkuManager $sku_manager,
                              ProductInfoHelper $product_info_helper,
                              CacheBackendInterface $cache,
                              ProductCacheManager $product_cache_manager,
                              SkuImagesHelper $sku_images_helper) {
    $this->innerService = $sku_image_manager;
    parent::__construct($module_handler,
      $config_factory,
      $entity_type_manager,
      $sku_manager,
      $product_info_helper,
      $cache,
      $product_cache_manager,
      $sku_images_helper
    );
  }

  /**
   * Helper for media thumbnails.
   *
   * @param array $thumbnails
   *   Thumbnails array.
   * @param array $media_item
   *   Media item.
   */
  protected function getThumbnailImagesFromMedia(array &$thumbnails, array $media_item) {
    // Fetch settings.
    $settings = $this->getCloudZoomDefaultSettings();
    $thumbnail_style = $settings['thumb_style'];
    $zoom_style = $settings['zoom_style'];
    $slide_style = $settings['slide_style'];

    if (!empty($media_item['file']) || !empty($media_item['pims_image']['url'])) {
      $file_uri = $media_item['file'] ?? '';

      // For asset type attribute we need below changes e.g. hm and cos.
      if (!empty($media_item['pims_image']['url']) && isset($media_item['pims_image']['styles'])) {
        $file_uri = $media_item['pims_image']['url'];
        $media_item = $media_item['pims_image'];
      }

      // Show original full image in the modal inside a draggable container.
      $original_image = file_create_url($file_uri);

      // Get Pims urls by styles.
      $image_small = $this->skuImagesHelper->getImageStyleUrl($media_item, $thumbnail_style);
      $image_zoom = $this->skuImagesHelper->getImageStyleUrl($media_item, $zoom_style);
      $image_medium = $this->skuImagesHelper->getImageStyleUrl($media_item, $slide_style);

      $thumbnails[] = [
        'thumburl' => $image_small,
        'mediumurl' => $image_medium,
        'zoomurl' => $image_zoom,
        'fullurl' => $original_image,
        'label' => $media_item['label'] ?? '',
        'type' => 'image',
      ];
    }
  }

  /**
   * Helper function to get image url.
   *
   * @param array $item
   *   Media Item.
   *
   * @return false|string
   *   Image url or false.
   */
  protected function getSwatchImageFromMedia(array $item) {
    return !empty($item['file'])
      ? file_create_url($item['file']) :
      FALSE;
  }

  /**
   * Get media items for particular SKU.
   *
   * This is CORE implementation to get media items from media array in SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Media items array.
   */
  public function getSkuMediaItems(SKUInterface $sku): array {
    $static = &drupal_static(__FUNCTION__, []);

    $static_id = implode(':', [
      $sku->getSku(),
      $sku->language()->getId(),
    ]);

    if (isset($static[$static_id])) {
      return $static[$static_id];
    }

    // phpcs:ignore
    $media = unserialize($sku->get('media')->getString());

    $this->moduleHandler->alter(
      'alshaya_acm_product_media_items', $media, $sku
    );

    foreach ($media ?? [] as $index => $media_item) {
      if (isset($media_item['disabled']) && $media_item['disabled'] == 1) {
        unset($media[$index]);
        continue;
      }
      $media_item = !empty($media_item) ? array_filter($media_item) : [];

      if (isset($media_item['file'])) {
        $media_item['drupal_uri'] = $media_item['file'];
      }

      $media[$index] = $media_item;
    }

    $static[$static_id] = $media;
    return $media;
  }

  /**
   * Helper function to get swatch image url.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return false|string
   *   Swatch image url or false.
   */
  public function getSwatchImageUrl(SKUInterface $sku) {
    // Let's never download images here, we should always download when
    // preparing gallery which is done before this.
    $swatch_product_image = $sku->getThumbnail(FALSE);

    // If we have image for the product.
    if (!empty($swatch_product_image['file'])) {
      return $swatch_product_image['file'];
    }

    return FALSE;
  }

}
