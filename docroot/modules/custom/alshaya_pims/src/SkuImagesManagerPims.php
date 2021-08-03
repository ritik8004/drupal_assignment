<?php

namespace Drupal\alshaya_pims;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
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
  const PRODUCT_MEDIA_CACHE_KEY = 'product_media_pims';

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
      self::PRODUCT_MEDIA_CACHE_KEY,
      (int) $check_parent_child,
      $context,
    ]);

    $cache = $this->productCacheManager->get($sku, $cache_key);

    if (is_array($cache)) {
      return $cache;
    }

    try {
      $skuForGallery = $check_parent_child ? $this->getSkuForGallery($sku, $check_parent_child) : $sku;
      $data = $this->productInfoHelper->getMedia($skuForGallery, $context) ?? NULL;

      foreach ($data['media_items']['images'] ?? [] as $key => $item) {
        if (empty($item['label'])) {
          $data['media_items']['images'][$key]['label'] = (string) $sku->label();
        }
      }

      $this->productCacheManager->set($sku, $cache_key, $data);
    }
    catch (\Exception $e) {
      $data = [];
    }

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

    $media = $this->getSkuMediaItems($sku);

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

  /**
   * Get thumbnails for gallery from media array.
   *
   * @param array $media
   *   Array of media items.
   * @param bool $get_main_image
   *   Whether to get main image as well or not.
   *
   * @return array
   *   Thumbnails.
   */
  public function getThumbnailsFromMedia(array $media, $get_main_image = FALSE) {
    $thumbnails = $media['thumbs'] ?? [];

    // Fetch settings.
    $settings = $this->getCloudZoomDefaultSettings();
    $thumbnail_style = $settings['thumb_style'];
    $zoom_style = $settings['zoom_style'];
    $slide_style = $settings['slide_style'];
    $main_image = $media['main'] ?? [];

    // Create our thumbnails to be rendered for zoom.
    foreach ($media['media_items']['images'] ?? [] as $media_item) {
      if (!empty($media_item['file'])) {
        $file_uri = $media_item['file'];

        // Show original full image in the modal inside a draggable container.
        $original_image = file_create_url($file_uri);

        // Get Pims urls by styles.
        $image_small = $this->skuImagesHelper->getImageStyleUrl($media_item, $thumbnail_style);
        $image_zoom = $this->skuImagesHelper->getImageStyleUrl($media_item, $zoom_style);
        $image_medium = $this->skuImagesHelper->getImageStyleUrl($media_item, $slide_style);

        if ($get_main_image && empty($main_image)) {
          $main_image = [
            'zoomurl' => $image_zoom,
            'mediumurl' => $image_medium,
            'label' => $media_item['label'],
          ];
        }

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
    $video_inserted_at_second_position = FALSE;
    foreach ($media['media_items']['videos'] ?? [] as $media_item) {
      $video_data = [];
      if (isset($media_item['video_url'])) {
        // @todo Receiving video_provider as NULL, should be set to youtube
        // or vimeo. Till then using $type as provider flag.
        $type = strpos($media_item['video_url'], 'youtube') ? 'youtube' : 'vimeo';
        $video_data = [
          'thumburl' => $media_item['file'],
          'url' => alshaya_acm_product_generate_video_embed_url($media_item['video_url'], $type),
          'video_title' => $media_item['video_title'],
          'video_desc' => $media_item['video_description'],
          'type' => $type,
          // @todo should this be config?
          'width' => 81,
          // @todo should this be config?
          'height' => 81,
        ];
      }
      else {
        $video_data = [
          'url' => file_create_url($media_item['drupal_uri']),
          'video_title' => $media_item['label'] ?? '',
          'type' => 'video',
        ];
      }
      // As per the requirement, we are placing the 1st video at
      // 2nd position in the PDP gallery and the rest of the
      // videos at the end of images if any.
      if (!$video_inserted_at_second_position) {
        array_splice($thumbnails, 1, 0, [$video_data]);
        $video_inserted_at_second_position = TRUE;
      }
      else {
        $thumbnails[] = $video_data;
      }
    }

    $return['thumbnails'] = $thumbnails;
    if ($get_main_image) {
      $return['main_image'] = $main_image;
    }

    return $return;
  }

  /**
   * Get Swatch Image url for PDP.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   *
   * @return string|null
   *   URL of swatch image or null
   */
  public function getPdpSwatchImageUrl(SKU $sku) {
    $media = $this->getSkuMediaItems($sku);

    $static = &drupal_static(__FUNCTION__, NULL);
    $static[$sku->getSku()] = NULL;

    foreach ($media as $item) {
      if (isset($item['roles'])
        && in_array(self::SWATCH_IMAGE_ROLE, $item['roles'])
        && !empty($item['file'])) {

        $static[$sku->getSku()] = file_create_url($item['file']);
        break;
      }
    }

    return $static[$sku->getSku()];
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

    $media = unserialize($sku->get('media')->getString());

    $this->moduleHandler->alter(
      'alshaya_acm_product_media_items', $media, $sku
    );

    foreach ($media ?? [] as $index => $media_item) {
      $media_item = !empty($media_item) ? array_filter($media_item) : [];

      if (isset($media_item['file'])) {
        $media_item['drupal_uri'] = $media_item['file'];
      }

      $media[$index] = $media_item;
    }

    $static[$static_id] = $media;
    return $media;
  }

}
