<?php

namespace Drupal\alshaya_acm_product;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\file\FileInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\file\Entity\File;
use Drupal\Component\Utility\Unicode;

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
   * Cache Backend object for "cache.data".
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Product Info Helper.
   *
   * @var \Drupal\acq_sku\ProductInfoHelper
   */
  protected $productInfoHelper;

  /**
   * Product display settings (alshaya_acm_product.display_settings).
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $productDisplaySettings;

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
   * @param \Drupal\acq_sku\ProductInfoHelper $product_info_helper
   *   Product Info Helper.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend object.
   */
  public function __construct(ModuleHandlerInterface $module_handler,
                              ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager,
                              SkuManager $sku_manager,
                              ProductInfoHelper $product_info_helper,
                              CacheBackendInterface $cache) {
    $this->moduleHandler = $module_handler;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->configFactory = $config_factory;
    $this->skuManager = $sku_manager;
    $this->productInfoHelper = $product_info_helper;
    $this->cache = $cache;

    $this->productDisplaySettings = $this->configFactory->get('alshaya_acm_product.display_settings');
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
   * Wrapper function to check if particular SKU has media(image/video) or not.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return bool
   *   TRUE if SKU has media(images/videos).
   */
  public function hasMedia(SKUInterface $sku) {
    $media = $this->getAllMedia($sku, FALSE);
    return !empty($media['images']) || !empty($media['videos']);
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
    try {
      $skuForGallery = $this->getSkuForGallery($sku, $check_parent_child);
      return $this->productInfoHelper->getMedia($skuForGallery, $context);
    }
    catch (\Exception $e) {
      // For configurable products with no children, we may not have any
      // child to get media items from.
      return [];
    }
  }

  /**
   * Get media items for particlar SKU.
   *
   * This is CORE implementation to get media items from media array in SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param null|string $default_label
   *   Default label to use for alt/title.
   *
   * @return array
   *   Media items array.
   */
  public function getSkuMediaItems(SKUInterface $sku, ?string $default_label = ''): array {
    /** @var \Drupal\acq_sku\Entity\SKU $sku */
    $plugin = $sku->getPluginInstance();

    if (empty($default_label) && $sku->bundle() == 'simple') {
      $parent = $plugin->getParentSku($sku);

      // Check if there is parent SKU available, we use label from that.
      if ($parent instanceof SKUInterface) {
        $default_label = $parent->label();
      }
    }

    $media = $sku->getMedia(TRUE, FALSE, $default_label);

    foreach ($media ?? [] as $index => $media_item) {
      if (!isset($media_item['media_type'])) {
        continue;
      }

      // Remove thumbnails only if it is not base image.
      if (isset($media_item['roles'])
        && !in_array('image', $media_item['roles'])
        && in_array('thumbnail', $media_item['roles'])) {

        unset($media[$index]);
      }
    }

    $return = [
      'images' => [],
      'videos' => [],
      'media_items' => [],
    ];

    // Process CORE media files.
    if (!empty($media)) {
      foreach ($media as $media_item) {
        if (!isset($media_item['media_type'])) {
          continue;
        }

        if ($media_item['media_type'] == 'image') {
          $url = $media_item['file']->url();
          $return['images'][$url] = $url;
        }
        elseif ($media_item['media_type'] == 'external-video') {
          $return['videos'][$media_item['video_url']] = $media_item['video_url'];
        }
      }
    }

    return $return;
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
      try {
        return $this->addFileObjects($return);
      }
      catch (\Exception $e) {
        // Do nothing and let code execution continue to get
        // fresh gallery.
      }
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

    // Remove thumbnails from media items.
    // @TODO: This is added for CORE-5026 and will be reworked in CORE-5208.
    foreach ($media ?? [] as $index => $media_item) {
      if (!isset($media_item['media_type'])) {
        continue;
      }

      if (isset($media_item['roles'])
        && in_array('thumbnail', $media_item['roles'])) {
        unset($media[$index]);
      }
    }

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

    // For simple children we need to add images from parent
    // if configured to do so.
    if ($sku->bundle() === 'simple' && !$check_parent_child && $this->addParentImagesInChild()) {
      /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
      $plugin = $sku->getPluginInstance();
      $parent = $plugin->getParentSku($sku);

      if ($parent instanceof SKUInterface) {
        $parent_media = $this->getAllMedia($parent, FALSE, $default_label);
        $return = array_merge_recursive($return, $parent_media);
      }
    }

    $this->skuManager->setProductCachedData(
      $sku,
      $cache_key,
      $this->removeFileObjects($return)
    );

    return $return;
  }

  /**
   * Wrapper function to get flag from config to show parent images in child.
   *
   * @return bool
   *   TRUE if we need to show parent images in child.
   */
  public function addParentImagesInChild() {
    $static = &drupal_static(__FUNCTION__, NULL);

    if ($static === NULL) {
      $static = (bool) $this->productDisplaySettings->get('show_parent_images_in_child');
    }

    return $static;
  }

  /**
   * Add file objects back to cached version of media.
   *
   * @param array $media
   *   Media array.
   *
   * @return array
   *   Processed media array.
   *
   * @throws \Exception
   *   When fails to load file from fid in cache.
   */
  private function addFileObjects(array $media) {
    if (empty($media['media_items']['images'])) {
      return $media;
    }

    foreach ($media['media_items']['images'] as &$item) {
      if (isset($item['fid'])) {
        $item['file'] = $this->fileStorage->load($item['fid']);

        if (!($item['file'] instanceof FileInterface)) {
          throw new \Exception('Failed to load file from fid in cache.');
        }
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
          if (($child instanceof SKUInterface) && ($this->hasMedia($child))) {
            $this->skuManager->setProductCachedData(
              $sku, $cache_key, $child->getSku()
            );
            return $child;
          }
        }
      }
    }

    // Lets return one from available OOS ones.
    foreach ($this->skuManager->getChildSkus($sku) as $child) {
      if ($this->hasMedia($child)) {
        $this->skuManager->setProductCachedData(
          $sku, $cache_key, $child->getSku()
        );
        return $child;
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

    try {
      $sku = $this->getSkuForGallery($sku);
    }
    catch (\Exception $e) {
      return [];
    }

    $media = $this->getAllMedia($sku);

    if (isset($media['media_items'], $media['media_items']['images'])
      && is_array($media['media_items']['images'])) {
      return reset($media['media_items']['images']);
    }

    return [];
  }

  /**
   * Get the url of the image of SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity object.
   * @param bool $absolute
   *   Flag to specify if absolute URL is required or relative.
   *
   * @return string
   *   Url of the image.
   */
  public function getFirstImageUrl(SKUInterface $sku, bool $absolute = FALSE) : string {
    // Load the first image.
    $media_image = $this->getFirstImage($sku);

    // If we have image for the product.
    if (!empty($media_image) && $media_image['file'] instanceof FileInterface) {
      $uri = $media_image['file']->getFileUri();
      $url = file_create_url($uri);
      return $absolute ? $url : file_url_transform_relative($url);
    }

    return '';
  }

  /**
   * Wrapper function to get sku for gallery considering color value.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   * @param string|null $color
   *   Color value.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   SKU to use for gallery.
   *
   * @throws \Exception
   */
  public function getSkuForGalleryWithColor(SKU $sku, $color = NULL): ?SKU {
    if (empty($color)) {
      try {
        return $this->getSkuForGallery($sku);
      }
      catch (\Exception $e) {
      }

      return NULL;
    }

    foreach ($this->skuManager->getPdpSwatchAttributes() as $attribute_code) {
      $sku_for_gallery = $this->skuManager->getChildSkuFromAttribute($sku, $attribute_code, $color);
      if ($sku_for_gallery instanceof SKUInterface) {
        return $sku_for_gallery;
      }
    }

    return NULL;
  }

  /**
   * Get SKU to use for gallery when no specific child is selected.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   * @param bool $check_parent_child
   *   Flag to mention if parent/child should be checked later.
   * @param string $case
   *   Case to override config.
   * @param \Drupal\acq_commerce\SKUInterface $preferred
   *   Preferred child.
   *
   * @return \Drupal\acq_commerce\SKUInterface
   *   SKU to be used for gallery.
   *
   * @throws \Exception
   */
  public function getSkuForGallery(SKUInterface $sku, $check_parent_child = TRUE, string $case = '', SKUInterface $preferred = NULL) {
    $configurable_use_parent_images = $this->productDisplaySettings->get('configurable_use_parent_images');
    $is_configurable = $sku->bundle() == 'configurable';

    if (!empty($case) && $configurable_use_parent_images != 'never') {
      $configurable_use_parent_images = $case;
    }

    $cache_key = implode(':', [
      'key' => 'sku_for_gallery',
      'flag' => (int) $check_parent_child,
      'case' => $case,
    ]);

    $cache = $this->skuManager->getProductCachedData($sku, $cache_key);

    if (is_array($cache)) {
      $child = SKU::loadFromSku($cache['sku']);
      if ($child instanceof SKUInterface) {
        return $child;
      }
    }

    $skuForGallery = $sku;

    switch ($configurable_use_parent_images) {
      case 'never':
        if ($is_configurable) {
          if ($preferred instanceof SKUInterface
            && $preferred->bundle() === 'simple'
            && $this->hasMedia($preferred)) {
            $child = $preferred;
          }
          else {
            $child = $this->getFirstChildWithMedia($sku);
          }

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
        break;

      case 'fallback':
        // Here we first check if images are there in child.
        // If not only then we use image from parent.
        if ($is_configurable) {
          if ($preferred instanceof SKUInterface
            && $preferred->bundle() === 'simple'
            && $this->hasMedia($preferred)) {
            $child = $preferred;
          }
          else {
            $child = $this->getFirstChildWithMedia($sku);
          }

          if ($child instanceof SKU) {
            $skuForGallery = $child;
          }
          // Check if parent has image before fallbacking to OOS children.
          elseif (!$this->hasMedia($sku)) {
            // Try to get first available child for OOS.
            $child = $this->skuManager->getFirstAvailableConfigurableChild($sku);
            if ($child instanceof SKU) {
              $skuForGallery = $child;
            }
          }
        }
        break;

      case 'always':
      default:
        // Case were we will show image from parent first, if not available
        // image from child, if still not - empty/default image.
        if ($check_parent_child) {
          if ($is_configurable) {
            if (!$this->hasMedia($sku)) {
              $child = $this->getFirstChildWithMedia($sku);
              if ($child instanceof SKU) {
                $skuForGallery = $child;
              }
            }
          }
          elseif ($this->hasMedia($sku) && $this->addParentImagesInChild()) {
            $skuForGallery = $sku;
          }
          else {
            // Always check parent first.
            $parent = $this->skuManager->getParentSkuBySku($sku);
            if ($parent instanceof SKUInterface) {
              return $this->getSkuForGallery($parent);
            }
          }
        }
        break;
    }

    $cache = ['sku' => $skuForGallery->getSku()];

    $this->skuManager->setProductCachedData($sku, $cache_key, $cache);

    return $skuForGallery;
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
   * @param bool $add_default_image
   *   Flag to mention if default image needs to be added or not.
   *
   * @return array
   *   Gallery.
   */
  public function getGallery(SKUInterface $sku, $context = 'search', $product_label = '', $add_default_image = TRUE) {
    $gallery = [];

    $display_thumbnails = $this->productDisplaySettings->get('image_thumb_gallery');

    switch ($context) {
      case 'search':
        // Invoke the alter hook to allow all modules to set the gallery.
        $this->moduleHandler->alter(
          'alshaya_acm_product_gallery', $gallery, $sku, $context
        );

        // Default logic if nothing done in any of the implemented alter hooks.
        if (empty($gallery)) {
          $search_main_image = $thumbnails = [];

          $media = $this->getAllMedia($sku);

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
          ];
        }

        break;

      case 'modal':
      case 'pdp':
      case 'modal-magazine':

        $media = $this->getAllMedia($sku);
        $mediaItems = $this->getThumbnailsFromMedia($media, TRUE);
        $thumbnails = $mediaItems['thumbnails'];
        $main_image = $mediaItems['main_image'];

        // Fetch settings.
        $settings = $this->getCloudZoomDefaultSettings();

        // If no main image and no video, use default image.
        if (empty($main_image) && $add_default_image && empty($media['media_items']['videos'])) {
          if (!empty($default_image = $this->getProductDefaultImage())) {
            $image_zoom = ImageStyle::load($settings['zoom_style'])
              ->buildUrl($default_image->getFileUri());
            $image_medium = ImageStyle::load($settings['slide_style'])
              ->buildUrl($default_image->getFileUri());

            $main_image = [
              'zoomurl' => $image_zoom,
              'mediumurl' => $image_medium,
              'label' => $sku->label(),
            ];

            $thumbnails[] = [
              'fullurl' => $default_image->url(),
              'label' => $sku->label(),
            ];
          }
        }

        // If either of main image or video is available.
        if (!empty($main_image) || !empty($media['media_items']['videos'])) {
          $config_name = ($context == 'modal') ? 'pdp_slider_items_settings.pdp_slider_items_number_cs_us' : 'pdp_gallery_pager_limit';
          $pdp_gallery_pager_limit = $this->configFactory->get('alshaya_acm_product.settings')
            ->get($config_name);

          $pager_flag = count($thumbnails) > $pdp_gallery_pager_limit ? 'pager-yes' : 'pager-no';

          $gallery = [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['gallery-wrapper'],
            ],
          ];

          $sku_identifier = strtolower(Html::cleanCssIdentifier($sku->getSku()));

          $labels = [
            '#theme' => 'product_labels',
            '#labels' => $this->skuManager->getLabels($sku, 'pdp'),
            '#sku' => $sku_identifier,
            '#mainsku' => $sku_identifier,
            '#type' => 'pdp',
          ];

          // Add PDP slider position class in template.
          $pdp_image_slider_position = $this->skuManager->getImageSliderPosition($sku);

          $library_array = [
            'alshaya_product_zoom/cloud_zoom_pdp_gallery',
            'alshaya_product_zoom/product.cloud_zoom',
          ];

          if ($context == 'pdp') {
            $library_array[] = 'alshaya_product_zoom/cloud_zoom';
          }

          if ($context == 'modal-magazine') {
            $library_array[] = 'alshaya_product_zoom/magazine_gallery';
          }

          $gallery['product_zoom'] = [
            '#theme' => 'product_zoom',
            '#mainImage' => $main_image,
            '#thumbnails' => $thumbnails,
            '#pager_flag' => $pager_flag,
            '#properties' => $this->getRelCloudZoom($settings),
            '#labels' => $labels,
            '#image_slider_position_pdp' => 'slider-position-' . $pdp_image_slider_position,
            '#attached' => [
              'library' => $library_array,
            ],
          ];
        }
        break;

      case 'pdp-magazine':
        $media = $this->getAllMedia($sku);
        $mediaItems = $this->getThumbnailsFromMedia($media, FALSE);
        $thumbnails = $mediaItems['thumbnails'];

        // If thumbnails available.
        if (!empty($thumbnails)) {
          $settings = $this->getCloudZoomDefaultSettings();
          $config_name = ($context == 'modal') ? 'pdp_slider_items_settings.pdp_slider_items_number_cs_us' : 'pdp_gallery_pager_limit';
          $pdp_gallery_pager_limit = $this->configFactory->get('alshaya_acm_product.settings')
            ->get($config_name);

          $pager_flag = count($thumbnails) > $pdp_gallery_pager_limit ? 'pager-yes' : 'pager-no';

          $gallery = [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['gallery-wrapper'],
            ],
          ];

          $sku_identifier = Unicode::strtolower(Html::cleanCssIdentifier($sku->getSku()));

          $labels = [
            '#theme' => 'product_labels',
            '#labels' => $this->skuManager->getLabels($sku, 'pdp'),
            '#sku' => $sku_identifier,
            '#mainsku' => $sku_identifier,
            '#type' => 'pdp',
          ];

          $gallery['alshaya_magazine'] = [
            '#theme' => 'alshaya_magazine',
            '#sku' => $sku,
            '#thumbnails' => $thumbnails,
            '#pager_flag' => $pager_flag,
            '#properties' => $this->getRelCloudZoom($settings),
            '#labels' => $labels,
            '#attached' => [
              'library' => [
                'alshaya_product_zoom/cloud_zoom',
                'alshaya_product_zoom/product.cloud_zoom',
                'alshaya_product_zoom/magazine_gallery',
              ],
            ],
          ];
        }
        break;
    }

    return $gallery;
  }

  /**
   * Get default settings for CloudZoom library.
   *
   * @return array
   *   Returns the default settings for CloudZoom library.
   */
  protected function getCloudZoomDefaultSettings() {
    return [
      'slide_style' => 'product_zoom_medium_606x504',
      'zoom_style' => 'product_zoom_large_800x800',
      'thumb_style' => '291x288',
      'zoom_width' => 'auto',
      'zoom_height' => 'auto',
      'zoom_position' => 'right',
      'adjust_x' => '0',
      'adjust_y' => '0',
      'tint' => '',
      'tint_opacity' => '0.25',
      'lens_opacity' => '0.85',
      'soft_focus' => 'false',
      'smooth_move' => '3',
    ];
  }

  /**
   * Get the rel attribute for Alshaya Product zoom.
   *
   * @param array $settings
   *   Product CloudZoom settings.
   *
   * @return string
   *   return the rel attribute.
   */
  protected function getRelCloudZoom(array $settings) {
    $string = '';
    $string .= "zoomWidth:'" . $settings['zoom_width'] . "'";
    $string .= ",zoomHeight:'" . $settings['zoom_height'] . "'";
    $string .= ",position:'" . $settings['zoom_position'] . "'";
    $string .= ",adjustX:'" . $settings['adjust_x'] . "'";
    $string .= ",adjustY:'" . $settings['adjust_y'] . "'";
    $string .= ",tint:'" . $settings['tint'] . "'";
    $string .= ",tintOpacity:'" . $settings['tint_opacity'] . "'";
    $string .= ",lensOpacity:'" . $settings['lens_opacity'] . "'";
    $string .= ",softFocus:" . $settings['soft_focus'];
    $string .= ",smoothMove:'" . $settings['smooth_move'] . "'";
    return $string;
  }

  /**
   * Get the default image url for the product.
   *
   * @return \Drupal\file\Entity\File|null
   *   File object.
   */
  public function getProductDefaultImage() {
    static $product_default_image;

    // If default image available in static cache, then use it.
    if (!empty($product_default_image)) {
      return $product_default_image;
    }

    // If cached version available.
    if ($cached_default_product_image = $this->cache->get('product_default_image')) {
      // Set in static cache.
      $product_default_image = $cached_default_product_image->data;
      return $product_default_image;
    }

    // Get file id from config.
    $default_image_fid = $this->configFactory->get('alshaya_acm_product.settings')->get('product_default_image');
    if (!empty($default_image_fid)) {
      $file = $this->fileStorage->load($default_image_fid);
      if ($file instanceof File) {
        // Set the cache.
        $this->cache->set('product_default_image', $file);
        // Set the static cache.
        $product_default_image = $file;
        return $product_default_image;
      }
    }

    return NULL;
  }

  /**
   * Get the default image url for the product.
   *
   * @param bool $absolute
   *   Flag to specify if absolute URL is required or relative.
   *
   * @return string
   *   Default image url.
   */
  public function getProductDefaultImageUrl(bool $absolute = FALSE) : string {
    $file = $this->getProductDefaultImage();

    if ($file) {
      $url = file_create_url($file->getFileUri());
      return $absolute ? $url : file_url_transform_relative($url);
    }

    return '';
  }

  /**
   * Get Swatches Data for particular configurable sku.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Swatches data.
   */
  public function getSwatchData(SKUInterface $sku): array {
    $swatches = [];
    $swatch_attributes = $this->configFactory->get('alshaya_acm_product.display_settings')->get('swatches')['pdp'];

    $combinations = $this->skuManager->getConfigurableCombinations($sku);
    foreach ($combinations['attribute_sku'] ?? [] as $attribute_code => $attribute_data) {
      // Process only for swatch attributes.
      if (!in_array($attribute_code, $swatch_attributes)) {
        continue;
      }

      $swatches['attribute_code'] = $attribute_code;

      foreach ($attribute_data as $value => $child_sku_codes) {
        foreach ($child_sku_codes as $child_sku_code) {
          $child = SKU::loadFromSku($child_sku_code);
          $data = $this->productInfoHelper->getValue($child, 'swatch', $attribute_code, '');
          if (empty($data)) {
            continue;
          }

          $data['value'] = $value;
          $swatches['swatches'][$value] = $data;

          break;
        }
      }

      // We expect only swatch attribute for any single product.
      break;
    }

    return $swatches;
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
  protected function getThumbnailsFromMedia(array $media, $get_main_image = FALSE) {
    $thumbnails = $media['thumbs'];

    // Fetch settings.
    $settings = $this->getCloudZoomDefaultSettings();
    $thumbnail_style = $settings['thumb_style'];
    $zoom_style = $settings['zoom_style'];
    $slide_style = $settings['slide_style'];
    $main_image = $media['main'];

    // Create our thumbnails to be rendered for zoom.
    foreach ($media['media_items']['images'] ?? [] as $media_item) {
      if ($media_item['file'] instanceof FileInterface) {
        $file_uri = $media_item['file']->getFileUri();

        // Show original full image in the modal inside a draggable container.
        $original_image = $media_item['file']->url();

        $image_small = ImageStyle::load($thumbnail_style)
          ->buildUrl($file_uri);
        $image_zoom = ImageStyle::load($zoom_style)->buildUrl($file_uri);
        $image_medium = ImageStyle::load($slide_style)->buildUrl($file_uri);

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
          'label' => $media_item['label'],
          'type' => 'image',
        ];
      }
    }
    foreach ($media['media_items']['videos'] ?? [] as $media_item) {
      // @TODO:
      // Receiving video_provider as NULL, should be set to youtube
      // or vimeo. Till then using $type as provider flag.
      $type = strpos($media_item['video_url'], 'youtube') ? 'youtube' : 'vimeo';
      $thumbnails[] = [
        'thumburl' => $media_item['file'],
        'url' => alshaya_acm_product_generate_video_embed_url($media_item['video_url'], $type),
        'video_title' => $media_item['video_title'],
        'video_desc' => $media_item['video_description'],
        'type' => $type,
        // @TODO: should this be config?
        'width' => 81,
        // @TODO: should this be config?
        'height' => 81,
      ];
    }

    $return['thumbnails'] = $thumbnails;
    if ($get_main_image) {
      $return['main_image'] = $main_image;
    }

    return $return;
  }

}
