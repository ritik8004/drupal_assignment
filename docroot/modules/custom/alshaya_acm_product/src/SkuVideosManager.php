<?php

namespace Drupal\alshaya_acm_product;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\alshaya_acm_product\Service\ProductCacheManager;

/**
 * Class SkuVideosManager.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuVideosManager {

  /**
   * SKU Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Product Info Helper.
   *
   * @var \Drupal\acq_sku\ProductInfoHelper
   */
  protected $productInfoHelper;

  /**
   * Product Cache Manager.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductCacheManager
   */
  protected $productCacheManager;

  /**
   * SkuVideosManager constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\acq_sku\ProductInfoHelper $product_info_helper
   *   Product Info Helper.
   * @param \Drupal\alshaya_acm_product\Service\ProductCacheManager $product_cache_manager
   *   Product Cache Manager.
   */
  public function __construct(SkuManager $sku_manager,
                              ProductInfoHelper $product_info_helper,
                              ProductCacheManager $product_cache_manager) {
    $this->skuManager = $sku_manager;
    $this->productInfoHelper = $product_info_helper;
    $this->productCacheManager = $product_cache_manager;
  }

  /**
   * Get video for particular SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   * @param string $context
   *   Context - pdp/pdp-magazine.
   *
   * @return array
   *   Render array of videos.
   */
  public function getVideo(SKUInterface $sku, $context = 'pdp') {
    $videos = $this->getProductVideo($sku, $context);

    if (empty($videos)) {
      return [];
    }

    $videos_render_array = [];
    foreach ($videos['media_items']['videos'] as $video) {
      $videos_render_array[] = [
        '#theme' => 'alshaya_videos',
        '#videoUrl' => file_create_url($video['drupal_uri']),
        '#videoLabel' => $video['label'],
      ];
    }

    return $videos_render_array;
  }

  /**
   * Get product video items.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context - pdp/pdp-magazine.
   *
   * @return array
   *   Processed video items.
   */
  public function getProductVideo(SKUInterface $sku, string $context): array {
    $cache_key = implode(':', [
      'product_video',
      $context,
    ]);

    $cache = $this->productCacheManager->get($sku, $cache_key);

    if (is_array($cache) && !empty($cache)) {
      return $cache;
    }

    try {
      $skuForVideo = $this->getSkuForVideo($sku);
      $data = $this->productInfoHelper->getValue($skuForVideo, 'videos', $context, '') ?? [];

      if (!empty($data)) {
        $this->productCacheManager->set($sku, $cache_key, $data);
      }
    }
    catch (\Exception $e) {
      $data = [];
    }

    return $data;
  }

  /**
   * Get SKU to get videos.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   *
   * @return \Drupal\acq_commerce\SKUInterface
   *   SKU to be used for video.
   *
   * @throws \Exception
   */
  public function getSkuForVideo(SKUInterface $sku) {
    $cache_key = implode(':', [
      'key' => 'sku_for_video',
      'sku' => $sku->getSku(),
    ]);

    $cache = $this->productCacheManager->get($sku, $cache_key);

    if ($cache) {
      $child = SKU::loadFromSku($cache);
      if ($child instanceof SKUInterface) {
        return $child;
      }
    }

    $skuForGallery = $sku;

    if ($sku->bundle() === 'configurable') {
      $child = $this->getFirstChildWithVideo($sku);

      // Try to get first valid in stock child.
      if ($child instanceof SKU) {
        $skuForGallery = $child;
      }
      else {
        // Try to get first available child for OOS.
        $child = $this->skuManager->getFirstAvailableConfigurableChild($sku);
        if ($child instanceof SKU) {
          $skuForGallery = $child;
        }
        else {
          throw new \Exception('No valid child found.', 404);
        }
      }
    }

    $this->productCacheManager->set($sku, $cache_key, $skuForGallery->getSku());

    return $skuForGallery;
  }

  /**
   * Get first child with video.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   First SKU entity with video if found else null.
   */
  public function getFirstChildWithVideo(SKUInterface $sku) {
    $cache_key = 'first_child_with_video';

    $child_sku = $this->productCacheManager->get($sku, $cache_key);
    if ($child_sku) {
      return SKU::loadFromSku($child_sku, $sku->language()->getId());
    }

    $children = $this->skuManager->getValidChildSkusAsString($sku);

    // First check from in-stock available ones.
    foreach ($children as $child_sku) {
      $child = SKU::loadFromSku($child_sku, $sku->language()->getId());
      if (($child instanceof SKUInterface) && ($this->hasVideo($child))) {
        $this->productCacheManager->set($sku, $cache_key, $child->getSku());
        return $child;
      }
    }

    // Lets return one from available OOS ones if not available from in-stock.
    foreach ($this->skuManager->getChildSkus($sku) as $child) {
      if ($this->hasVideo($child)) {
        $this->productCacheManager->set($sku, $cache_key, $child->getSku());
        return $child;
      }
    }

    return NULL;
  }

  /**
   * Wrapper function to check if particular SKU has video or not.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return bool
   *   TRUE if SKU has videos.
   */
  public function hasVideo(SKUInterface $sku) {
    $media = $this->getProductVideo($sku, 'pdp');
    return !empty($media);
  }

}
