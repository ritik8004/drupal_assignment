<?php

namespace Drupal\alshaya_acm_product;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * File storage.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * SkuImagesManager constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   */
  public function __construct(ModuleHandlerInterface $module_handler,
                              ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager,
                              SkuManager $sku_manager) {
    $this->moduleHandler = $module_handler;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->configFactory = $config_factory;
    $this->skuManager = $sku_manager;
  }

  /**
   * Utility function to return all media items for a SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param bool $check_parent_child
   *   Check parent or child SKUs.
   * @param string $default_label
   *   Default value for alt/title.
   *
   * @return array
   *   Array of media files.
   */
  public function getAllMediaItems(SKUInterface $sku, $check_parent_child = FALSE, $default_label = '') {
    $media = $this->getAllMedia($sku, $check_parent_child, $default_label);
    $media_items = [];
    foreach ($media['media_items'] ?? [] as $items) {
      $media_items = array_merge($media_items, $items);
    }
    return $media_items;
  }

  /**
   * Utility function to return all media files for a SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param bool $check_parent_child
   *   Check parent or child SKUs.
   * @param string $default_label
   *   Default value for alt/title.
   *
   * @return array
   *   Array of media files.
   */
  public function getAllMedia(SKUInterface $sku, $check_parent_child = FALSE, $default_label = '') {
    // Here for_sku means it can be in parent or child.
    // And from_sku means specifically for this SKU.
    $cache_key = $check_parent_child ? 'media_for_sku' : 'media_from_sku';

    $return = $this->skuManager->getProductCachedData($sku, $cache_key);

    if (is_array($return)) {
      return $this->addFileObjects($return);
    }

    $plugin = $sku->getPluginInstance();

    if (empty($default_label) && $sku->bundle() == 'simple') {
      $parent = $plugin->getParentSku($sku);

      // Check if there is parent SKU available, we use label from that.
      if ($parent instanceof SKUInterface) {
        $default_label = $parent->label();
      }
    }

    $media = $sku->getMedia(TRUE, FALSE, $default_label);

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

    $return['main'] = $main;
    $return['thumbs'] = $thumbs;

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
          return $this->getAllMedia($child, FALSE, $default_label);
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

    $this->skuManager->setProductCachedData(
      $sku,
      $cache_key,
      $this->removeFileObjects($return)
    );

    return $return;
  }

  /**
   * Add file objects back to cached version of media.
   *
   * @param array $media
   *   Media array.
   *
   * @return array
   *   Processed media array.
   */
  private function addFileObjects(array $media) {
    if (empty($media['media_items']['images'])) {
      return $media;
    }

    foreach ($media['media_items']['images'] as &$item) {
      if (isset($item['fid'])) {
        $item['file'] = $this->fileStorage->load($item['fid']);
      }
    }

    return $media;
  }

  /**
   * Remove file objects for caching media.
   *
   * @param array $media
   *   Media array.
   *
   * @return array
   *   Processed media array.
   */
  private function removeFileObjects(array $media) {
    if (empty($media['media_items']['images'])) {
      return $media;
    }

    foreach ($media['media_items']['images'] as &$item) {
      unset($item['file']);
    }

    return $media;
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
    $cache_key = 'first_child_with_media';

    $child_sku = $this->skuManager->getProductCachedData($sku, $cache_key);
    if ($child_sku) {
      return SKU::loadFromSku($child_sku, $sku->language()->getId());
    }

    $combinations = $this->skuManager->getConfigurableCombinations($sku);

    foreach ($combinations['attribute_sku'] ?? [] as $children) {
      foreach ($children as $child_skus) {
        foreach ($child_skus as $child_sku) {
          $child = SKU::loadFromSku($child_sku, $sku->language()->getId());
          $media = $this->getAllMedia($child, FALSE);
          if ($media['images'] || $media['videos']) {
            $this->skuManager->setProductCachedData(
              $sku, $cache_key, $child->getSku()
            );
            return $child;
          }
        }
      }
    }

    return NULL;
  }

  /**
   * Get first image from media to display as list.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   *
   * @return array
   *   Media item array.
   */
  public function getFirstImage(SKUInterface $sku) {
    $check_parent_child = TRUE;

    try {
      $sku = $this->getSkuForGallery($sku, $check_parent_child);
    }
    catch (\Exception $e) {
      return [];
    }

    $media = $this->getAllMedia($sku, $check_parent_child);

    if (isset($media['media_items'], $media['media_items']['images'])
      && is_array($media['media_items']['images'])) {
      return reset($media['media_items']['images']);
    }

    return [];
  }

  /**
   * Get SKU to use for gallery when no specific child is selected.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   * @param bool $check_parent_child
   *   Flag (by reference) to mention if parent/child should be checked later.
   *
   * @return \Drupal\acq_commerce\SKUInterface
   *   SKU to be used for gallery.
   *
   * @throws \Exception
   */
  public function getSkuForGallery(SKUInterface $sku, &$check_parent_child) {
    $config = $this->configFactory->get('alshaya_acm_product.display_settings');
    $configurable_use_parent_images = $config->get('configurable_use_parent_images');
    $is_configurable = $sku->bundle() == 'configurable';

    switch ($configurable_use_parent_images) {
      case 'never':
        // Case were we will show default/empty gallery but never use
        // from parent.
        $check_parent_child = FALSE;

        if ($is_configurable) {
          $child = $this->getFirstChildWithMedia($sku);

          // Try to get first valid in stock child.
          if ($child instanceof SKU) {
            $sku = $child;
          }
          else {
            // Try to get first available child for OOS.
            $child = $this->skuManager->getFirstAvailableConfigurableChild($sku);
            if ($child instanceof SKU) {
              $sku = $child;
            }
            else {
              throw new \Exception('No valid child found.', 404);
            }
          }
        }
        break;

      case 'fallback':
        // Here we first check if images are there in child.
        // If not only then we use image from parent.
        if ($is_configurable) {
          $check_parent_child = FALSE;
          $child = $this->getFirstChildWithMedia($sku);
          if ($child instanceof SKU) {
            $sku = $child;
          }
        }
        break;

      case 'always':
      default:
        // Case were we will show image from parent first, if not available
        // image from child, if still not - empty/default image.
        // Let it execute as is, this is default code.
        break;
    }

    return $sku;
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
   * @param bool $check_parent_child
   *   Flag to mention if parent/child should be checked later.
   *
   * @return array
   *   Gallery.
   */
  public function getGallery(SKUInterface $sku, $context = 'search', $product_label = '', $check_parent_child = TRUE) {
    $gallery = [];

    $config = $this->configFactory->get('alshaya_acm_product.display_settings');
    $display_thumbnails = $config->get('image_thumb_gallery');

    try {
      $sku = $this->getSkuForGallery($sku, $check_parent_child);
    }
    catch (\Exception $e) {
      return [];
    }

    switch ($context) {
      case 'search':
        // Invoke the alter hook to allow all modules to set the gallery.
        $this->moduleHandler->alter(
          'alshaya_acm_product_gallery', $gallery, $sku, $context
        );

        // Default logic if nothing done in any of the implemented alter hooks.
        if (empty($gallery)) {
          $search_main_image = $thumbnails = [];

          $media = $this->getAllMedia($sku, $check_parent_child);

          // Loop through all media items and prepare thumbnails array.
          foreach ($media['media_items']['images'] ?? [] as $media_item) {
            // For now we are displaying only image slider on search results
            // page and PLP.
            $media_item['label'] = $product_label;
            if (empty($search_main_image)) {
              $search_main_image = $this->skuManager->getSkuImage($media_item, '291x288');
            }

            if ($display_thumbnails) {
              $thumbnails[] = $this->skuManager->getSkuImage($media_item, '291x288', '291x288');
            }
          }

          $gallery = [
            '#theme' => 'alshaya_search_gallery',
            '#mainImage' => $search_main_image,
            '#thumbnails' => $thumbnails,
            '#attached' => [
              'drupalSettings' => [
                'plp_slider' => $this->configFactory->get('alshaya_acm_product.display_settings')->get('plp_slider'),
              ],
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
