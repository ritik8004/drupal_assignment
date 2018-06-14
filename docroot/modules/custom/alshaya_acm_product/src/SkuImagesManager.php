<?php

namespace Drupal\alshaya_acm_product;

use Drupal\acq_commerce\SKUInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class SkuImagesManager.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuImagesManager {

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * SkuImagesManager constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Utility function to return all media files for a SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param bool $check_parent
   *   Flag to specify if we should check parent SKU when nothing in child.
   *
   * @return array
   *   Array of media files.
   */
  public function getAllMedia(SKUInterface $sku, $check_parent = FALSE) {
    /** @var \Drupal\acq_sku\Entity\SKU $sku */
    // @TODO: Think of static cache when using it with hook_node_view().
    $media = $sku->getMedia();

    $return = [
      'images' => [],
      'videos' => [],
    ];

    // We will use below variables for alter hooks.
    $main = [];
    $thumbs = [];

    // Invoke the alter hook to allow all modules to update the element.
    $this->moduleHandler->alter('acq_sku_pdp_gallery_media', $main, $thumbs, $sku);

    // Avoid notices and warnings in local.
    if ($check_parent && empty($media) && empty($main)) {
      /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
      $plugin = $sku->getPluginInstance();
      $parent = $plugin->getParentSku($sku);

      // Check once if there is parent SKU available, use media files of parent.
      if ($parent instanceof SKUInterface) {
        return $this->getAllMedia($parent);
      }
    }

    // Process CORE media files.
    if (!empty($media)) {
      foreach ($media as $media_item) {
        if (!isset($media_item['media_type'])) {
          continue;
        }

        if ($media_item['media_type'] == 'image') {
          // Show original full image in the modal inside a draggable container.
          $url = $media_item['file']->url();
          $return['images'][$url] = $url;
        }
        elseif ($media_item['media_type'] == 'external-video') {
          $return['videos'][$media_item['video_url']] = $media_item['video_url'];
        }
      }
    }

    // Add main image provided by other modules.
    if ($main) {
      $url = $main['mediumurl']->toString();
      $return['images'][$url] = $url;
    }

    // Add all thumbnails provided by other modules.
    foreach ($thumbs as $thumb) {
      $url = $thumb['mediumurl']->toString();
      $return['images'][$url] = $url;
    }

    return $return;
  }

}
