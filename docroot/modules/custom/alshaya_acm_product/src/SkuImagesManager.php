<?php

namespace Drupal\alshaya_acm_product;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Url;

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
   * SKU Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * SkuImagesManager constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   */
  public function __construct(ModuleHandlerInterface $module_handler,
                              ConfigFactoryInterface $config_factory,
                              SkuManager $sku_manager) {
    $this->moduleHandler = $module_handler;
    $this->configFactory = $config_factory;
    $this->skuManager = $sku_manager;
  }

  /**
   * Utility function to return all media files for a SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param bool $check_parent_child
   *   Flag to specify if we should check parent SKU when nothing in child.
   *
   * @return array
   *   Array of media files.
   */
  public function getAllMedia(SKUInterface $sku, $check_parent_child = FALSE) {
    $media = $sku->getMedia();

    $return = [
      'images' => [],
      'videos' => [],
      'media_items' => [],
    ];

    // We will use below variables for alter hooks.
    $main = [];
    $thumbs = [];

    // Invoke the alter hook to allow all modules to update the element.
    $this->moduleHandler->alter('acq_sku_pdp_gallery_media', $main, $thumbs, $sku);

    // Avoid notices and warnings in local.
    if ($check_parent_child && empty($media) && empty($main)) {
      if ($sku->bundle() == 'simple') {
        /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
        $plugin = $sku->getPluginInstance();
        $parent = $plugin->getParentSku($sku);

        // Check if there is parent SKU available, use media files of parent.
        if ($parent instanceof SKUInterface) {
          return $this->getAllMedia($parent);
        }
      }
      elseif ($sku->bundle() == 'configurable') {
        $child = $this->getFirstChildWithMedia($sku);

        // Check if there is child SKU available, use media files of child.
        if ($child instanceof SKUInterface) {
          return $this->getAllMedia($child);
        }
      }
    }

    // Process CORE media files.
    if (!empty($media)) {
      foreach ($media as $media_item) {
        if (!isset($media_item['media_type'])) {
          continue;
        }

        if ($media_item['media_type'] == 'image') {
          $url = $media_item['file']->url();
          $return['images'][$url] = $url;
          $return['media_items']['images'][] = $media_item;
        }
        elseif ($media_item['media_type'] == 'external-video') {
          $return['videos'][$media_item['video_url']] = $media_item['video_url'];
          $return['media_items']['videos'][] = $media_item;
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

  /**
   * Get first child with media.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   First SKU entity with media if found else null.
   */
  public function getFirstChildWithMedia(SKUInterface $sku) {
    $cache_key = 'first_child_sku_with_media_' . $sku->getSku() . '_' . $sku->language()->getId();

    $child_sku = $this->skuManager->getProductCachedData($sku, $cache_key);
    if ($child_sku) {
      return SKU::loadFromSku($child_sku, $sku->language()->getId());
    }

    $childs = $this->skuManager->getChildSkus($sku);

    foreach ($childs as $child) {
      $media = $this->getAllMedia($child, FALSE);
      if ($media['images'] || $media['videos']) {
        $this->skuManager->setProductCachedData($sku, $cache_key, $child->getSku());
        return $child;
      }
    }

    return NULL;
  }

  /**
   * Get gallery for particular SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   * @param string $context
   *   Context - pdp/search/modal/teaser.
   * @param string $product_label
   *   Translated product label to use in alt/title.
   *
   * @return array
   *   Gallery.
   */
  public function getGallery(SKUInterface $sku, $context = 'search', $product_label = '') {
    $gallery = [];

    $display_thumbnails = $this->configFactory->get('alshaya_acm_product.display_settings')->get('image_thumb_gallery');

    switch ($context) {
      case 'search':
        // Invoke the alter hook to allow all modules to set the gallery.
        $this->moduleHandler->alter(
          'alshaya_acm_product_gallery', $gallery, $sku, $context
        );

        // Default logic if nothing done in any of the implemented alter hooks.
        if (empty($gallery)) {
          $search_main_image = $thumbnails = [];

          $media = $this->getAllMedia($sku, TRUE);

          // Loop through all media items and prepare thumbnails array.
          foreach ($media['media_items']['images'] ?? [] as $media_item) {
            // For now we are displaying only image slider on search results
            // page and PLP.
            $media_item['label'] = $product_label;
            if (empty($search_main_image)) {
              $search_main_image = $this->skuManager->getSkuImage($media_item, '291x288');
            }

            if ($display_thumbnails) {
              $thumbnails[] = $this->skuManager->getSkuImage($media_item, '59x60', '291x288');
            }
          }

          $gallery = [
            '#theme' => 'alshaya_search_gallery',
            '#mainImage' => $search_main_image,
            '#thumbnails' => $thumbnails,
            '#attached' => [
              'library' => [
                'alshaya_search/alshaya_search',
              ],
            ],
          ];
        }

        // Finally use default image if still empty.
        if (empty($gallery)) {
          $default_image = _alshaya_acm_product_get_product_default_main_image();
          if ($default_image) {
            $gallery = [
              '#theme' => 'alshaya_assets_gallery',
              '#mainImage' => [
                'url' => Url::fromUri(file_create_url($default_image->getFileUri())),
                'class' => 'product-default-image',
              ],
              '#label' => $product_label,
              '#attached' => [
                'library' => [
                  'alshaya_search/alshaya_search',
                ],
              ],
            ];
          }
        }
        break;

      case 'pdp':
        // @TODO: Copy alshaya_acm_product_get_gallery() here.
        break;
    }

    return $gallery;
  }

}
