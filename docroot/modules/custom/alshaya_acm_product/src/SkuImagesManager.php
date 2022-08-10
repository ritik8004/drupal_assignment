<?php

namespace Drupal\alshaya_acm_product;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\acq_sku\ProductInfoHelper;
use Drupal\alshaya_acm_product\Service\ProductCacheManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\file\FileInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\file\Entity\File;

/**
 * Class Sku Images Manager.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuImagesManager {

  use LoggerChannelTrait;

  public const BASE_IMAGE_ROLE = 'image';
  public const SWATCH_IMAGE_ROLE = 'swatch_image';

  // Cache key used for product media.
  public const PRODUCT_MEDIA_CACHE_KEY = 'product_media';

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
   * Product Cache Manager.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductCacheManager
   */
  protected $productCacheManager;

  /**
   * Sku images helper.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesHelper
   */
  protected $skuImagesHelper;

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
   * @param \Drupal\alshaya_acm_product\Service\ProductCacheManager $product_cache_manager
   *   Product Cache Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesHelper $images_helper
   *   Sku images helper.
   */
  public function __construct(ModuleHandlerInterface $module_handler,
                              ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager,
                              SkuManager $sku_manager,
                              ProductInfoHelper $product_info_helper,
                              CacheBackendInterface $cache,
                              ProductCacheManager $product_cache_manager,
                              SkuImagesHelper $images_helper) {
    $this->moduleHandler = $module_handler;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->configFactory = $config_factory;
    $this->skuManager = $sku_manager;
    $this->productInfoHelper = $product_info_helper;
    $this->cache = $cache;
    $this->productCacheManager = $product_cache_manager;
    $this->skuImagesHelper = $images_helper;

    $this->productDisplaySettings = $this->configFactory->get('alshaya_acm_product.display_settings');
  }

  /**
   * Utility function to return all media items for a SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param bool $check_parent_child
   *   Check parent or child SKUs.
   *
   * @return array
   *   Array of media files.
   */
  public function getAllMediaItems(SKUInterface $sku, $check_parent_child = FALSE) {
    $media = $this->getProductMedia($sku, 'pdp', $check_parent_child);
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
    $media = $this->getProductMedia($sku, 'pdp', FALSE);
    return !empty($media);
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
      static::PRODUCT_MEDIA_CACHE_KEY,
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
    catch (\Exception) {
      $data = [];
    }

    return $data;
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

    $media = $sku->getMedia();

    $this->moduleHandler->alter(
      'alshaya_acm_product_media_items', $media, $sku
    );

    foreach ($media ?? [] as $index => $media_item) {
      $media_item = !empty($media_item) ? array_filter($media_item) : [];

      if (isset($media_item['file']) && $media_item['file'] instanceof FileInterface) {
        $media_item['drupal_uri'] = $media_item['file']->getFileUri();
        unset($media_item['file']);
      }

      $media[$index] = $media_item;
    }

    $static[$static_id] = $media;
    return $media;
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

    $child_sku = $this->productCacheManager->get($sku, $cache_key);
    if ($child_sku) {
      return SKU::loadFromSku($child_sku, $sku->language()->getId());
    }

    $children = $this->skuManager->getValidChildSkusAsString($sku);

    // First check from in-stock available ones.
    foreach ($children as $child_sku) {
      $child = SKU::loadFromSku($child_sku, $sku->language()->getId());
      if (($child instanceof SKUInterface) && ($this->hasMedia($child))) {
        $this->productCacheManager->set($sku, $cache_key, $child->getSku());
        return $child;
      }
    }

    // Lets return one from available OOS ones if not available from in-stock.
    foreach ($this->skuManager->getChildSkus($sku) as $child) {
      if ($this->hasMedia($child)) {
        $this->productCacheManager->set($sku, $cache_key, $child->getSku());
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
   * @param string $context
   *   Context for image.
   * @param bool $add_default_image
   *   Flag to mention if default image needs to be added or not.
   *
   * @return array
   *   Media item array.
   */
  public function getFirstImage(SKUInterface $sku, string $context = 'plp', $add_default_image = FALSE) {
    $first_image = [];

    try {
      $sku = $this->getSkuForGallery($sku);
    }
    catch (\Exception) {
      return [];
    }

    $media = $this->getProductMedia($sku, $context, FALSE);

    if (isset($media['media_items'], $media['media_items']['images'])
      && is_array($media['media_items']['images'])) {
      $first_image = reset($media['media_items']['images']);
    }
    elseif ($add_default_image) {
      $default_image = $this->getProductDefaultImage();
      if (!empty($default_image)) {
        $first_image = [
          'label' => '',
          'media_type' => 'image',
          'fid' => $default_image->id(),
          'drupal_uri' => $default_image->getFileUri(),
        ];
      }
    }

    return $first_image;
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
    if (!empty($media_image['drupal_uri'])) {
      $url = file_create_url($media_image['drupal_uri']);
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
      catch (\Exception) {
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
   * Get gallery for all the colors of product.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   * @param string $context
   *   Gallery context.
   *
   * @return array
   *   Galleries for all color as array.
   */
  public function getAllColorGallery(SKU $sku, $context = 'search') {
    $listing_swatch_attributes = $this->skuManager->getProductListingSwatchAttributes();
    if (empty($listing_swatch_attributes)) {
      return [];
    }

    $listing_swatch_attribute = reset($listing_swatch_attributes);

    $combinations = $this->skuManager->getConfigurableCombinations($sku);

    $color_attribute = NULL;
    foreach ($this->skuManager->getPdpSwatchAttributes() as $attribute_code) {
      if (isset($combinations['attribute_sku'][$attribute_code])) {
        $color_attribute = $attribute_code;
        break;
      }
    }

    if (empty($color_attribute)) {
      return [];
    }

    $galleries = [];

    foreach ($combinations['attribute_sku'][$color_attribute] as $variants) {
      foreach ($variants as $variant_sku) {
        $variant = SKU::loadFromSku($variant_sku);
        $gallery = $this->getGallery($variant, $context);

        if (!empty($gallery) && !empty($gallery['#mainImage'])) {
          $color = $variant->get('attr_' . $listing_swatch_attribute)->getString();

          $galleries[$color] = [
            'color' => $color,
            'attribute' => $listing_swatch_attribute,
            'gallery' => $gallery,
            'id' => $variant->id(),
          ];

          break;
        }
      }
    }

    return $galleries;
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

    if ($this->skuManager->isSkuFreeGift($sku)) {
      $configurable_use_parent_images = 'fallback';
    }
    elseif (!empty($case) && $configurable_use_parent_images != 'never') {
      $configurable_use_parent_images = $case;
    }

    $cache_key = implode(':', [
      'key' => 'sku_for_gallery',
      'flag' => (int) $check_parent_child,
      'case' => $case,
    ]);

    $cache = $this->productCacheManager->get($sku, $cache_key);

    if ($cache) {
      $child = SKU::loadFromSku($cache);
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

    $this->productCacheManager->set($sku, $cache_key, $skuForGallery->getSku());

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
    $pdp_gallery_type = \Drupal::config('alshaya_acm_product.settings')->get('pdp_gallery_type');
    // Set Classic gallery to COS Magazine layout PDP.
    if ($pdp_gallery_type == 'classic' && $context == 'pdp-magazine') {
      $context = 'pdp';
    }

    $media = $this->getProductMedia($sku, $context, FALSE);

    if (empty($media) && !$add_default_image) {
      return [];
    }

    switch ($context) {
      case 'search':
        $search_main_image = $thumbnails = $search_hover_image = [];

        // Loop through all media items and prepare thumbnails array.
        foreach ($media['media_items']['images'] ?? [] as $media_item) {
          // For now we are displaying only image slider on search results
          // page and PLP.
          if (!empty($media_item)) {
            if (empty($search_main_image)) {
              $main_image = $this->skuImagesHelper->getSkuImage($media_item, SkuImagesHelper::STYLE_PRODUCT_LISTING);
              if (!empty($main_image)) {
                $search_main_image = $main_image;
              }
            }
            elseif ($this->productDisplaySettings->get('gallery_show_hover_image')) {
              $hover_image = $this->skuImagesHelper->getSkuImage($media_item, SkuImagesHelper::STYLE_PRODUCT_LISTING);
              if (!empty($hover_image)) {
                $search_hover_image = $hover_image;
              }
            }

            if ($this->productDisplaySettings->get('image_thumb_gallery')) {
              $thumbnail = $this->skuImagesHelper->getSkuImage(
                $media_item,
                SkuImagesHelper::STYLE_PRODUCT_LISTING,
                SkuImagesHelper::STYLE_PRODUCT_LISTING
              );
              if (!empty($thumbnail)) {
                $thumbnails[] = $thumbnail;
              }
            }
          }
        }

        $losanges = [
          '#theme' => 'product_labels',
          '#labels' => $this->skuManager->getLabels($sku, 'plp'),
          '#sku' => strtolower(Html::cleanCssIdentifier($sku->getSku())),
          '#mainsku' => strtolower(Html::cleanCssIdentifier($sku->getSku())),
          '#type' => 'plp',
        ];

        $gallery['#losanges'] = $losanges;

        if ($this->productDisplaySettings->get('gallery_show_hover_image')) {
          $gallery = [
            '#theme' => 'alshaya_assets_gallery',
            '#mainImage' => $search_main_image,
            '#losanges' => $losanges,
          ];

          if ($search_hover_image) {
            if (empty($search_hover_image['#attributes'])) {
              $search_hover_image['#attributes'] = [];
            }
            $search_hover_image['#attributes']['loading'] = 'lazy';
            $gallery['#hoverImage'] = $search_hover_image;
          }
        }
        else {
          $gallery = [
            '#theme' => 'alshaya_search_gallery',
            '#mainImage' => $search_main_image,
            '#thumbnails' => $thumbnails,
            '#losanges' => $losanges,
          ];
        }

        break;

      case 'modal':
      case 'pdp':
      case 'modal-magazine':
      case 'matchback':
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
              'zoomurl' => $image_zoom,
              'fullurl' => $default_image->createFileUrl(),
              'label' => $sku->label(),
            ];
          }
        }

        // If either of main image or video is available.
        if (!empty($main_image) || !empty($media['media_items']['videos'])) {
          $config_name = ($context == 'modal') ? 'pdp_slider_items_settings.pdp_slider_items_number_cs_us' : 'pdp_gallery_pager_limit';
          $pdp_gallery_pager_limit = $this->configFactory->get('alshaya_acm_product.settings')
            ->get($config_name);

          $pager_flag = (is_countable($thumbnails) ? count($thumbnails) : 0) > $pdp_gallery_pager_limit ? 'pager-yes' : 'pager-no';

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

          $theme = $context == 'matchback' ? 'matchback_gallery' : 'product_zoom';
          $gallery[$theme] = [
            '#theme' => $theme,
            '#mainImage' => $main_image,
            '#thumbnails' => $thumbnails,
            '#pager_flag' => $pager_flag,
            '#labels' => $labels,
            '#attached' => [
              'library' => $library_array,
            ],
          ];
        }
        break;

      case 'pdp-magazine':
        $mediaItems = $this->getThumbnailsFromMedia($media, FALSE);
        $thumbnails = $mediaItems['thumbnails'];

        // If thumbnails available.
        if (!empty($thumbnails)) {
          $config_name = ($context == 'modal') ? 'pdp_slider_items_settings.pdp_slider_items_number_cs_us' : 'pdp_gallery_pager_limit';
          $pdp_gallery_pager_limit = $this->configFactory->get('alshaya_acm_product.settings')
            ->get($config_name);

          $pager_flag = (is_countable($thumbnails) ? count($thumbnails) : 0) > $pdp_gallery_pager_limit ? 'pager-yes' : 'pager-no';

          $gallery = [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['gallery-wrapper'],
            ],
          ];

          $sku_identifier = mb_strtolower(Html::cleanCssIdentifier($sku->getSku()));

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
      'slide_style' => SkuImagesHelper::STYLE_PRODUCT_SLIDE,
      'zoom_style' => SkuImagesHelper::STYLE_PRODUCT_ZOOM,
      'thumb_style' => SkuImagesHelper::STYLE_PRODUCT_THUMBNAIL,
    ];
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
   * Helper function to get image url.
   *
   * @param array $item
   *   Media Item.
   *
   * @return false|string
   *   Image url or false.
   */
  protected function getSwatchImageFromMedia(array $item) {
    return !empty($item['drupal_uri'])
      ? file_create_url($item['drupal_uri']) :
      FALSE;
  }

  /**
   * Get Swatch Image url for PDP.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   * @param string $media_role
   *   Media role for the swatch attribute.
   *
   * @return string|null
   *   URL of swatch image or null
   */
  public function getPdpSwatchImageUrl(SKU $sku, $media_role = self::SWATCH_IMAGE_ROLE) {
    $media = $this->getSkuMediaItems($sku);

    $static = &drupal_static(__FUNCTION__, NULL);
    $static[$sku->getSku()] = NULL;

    foreach ($media as $item) {
      if (isset($item['roles'])
        && in_array($media_role, $item['roles'])) {
        $image = $this->getSwatchImageFromMedia($item);
        if ($image) {
          $static[$sku->getSku()] = $image;
          break;
        }
      }
    }

    return $static[$sku->getSku()];
  }

  /**
   * Get Swatches Data for particular configurable sku.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context in which we need to get swatch data.
   *
   * @return array
   *   Swatches data.
   */
  public function getSwatchData(SKUInterface $sku, $context = NULL): array {
    $swatches = [];
    $swatch_attributes = $this->skuManager->getPdpSwatchAttributes();

    if ($context && $context == 'plp') {
      // Get swatch attributes to exclude on PLP.
      $swatch_attributes_to_exclude = $this->skuManager->getSwatchAttributesToExcludeOnPlp();
      if (!empty($swatch_attributes_to_exclude)) {
        $swatch_attributes = array_diff($swatch_attributes, $swatch_attributes_to_exclude);
      }
    }

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
          $data['child_sku_code'] = $child_sku_code;
          $data['child_id'] = $child->id();
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
   * Get all Swatches Data for particular configurable sku.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Swatches data.
   */
  public function getAllSwatchData(SKUInterface $sku): array {
    $swatches = [];
    $swatch_attributes = $this->skuManager->getPdpSwatchAttributes();

    $combinations = $this->skuManager->getConfigurableCombinations($sku);
    foreach ($combinations['attribute_sku'] ?? [] as $attribute_code => $attribute_data) {
      // Process only for swatch attributes.
      if (!in_array($attribute_code, $swatch_attributes)) {
        continue;
      }

      foreach ($attribute_data as $value => $child_sku_codes) {
        foreach ($child_sku_codes as $child_sku_code) {
          $child = SKU::loadFromSku($child_sku_code);
          $data = $this->productInfoHelper->getValue($child, 'swatch', $attribute_code, '');
          if (empty($data)) {
            continue;
          }

          $data['value'] = $value;
          $data['child_sku_code'] = $child_sku_code;
          $data['child_id'] = $child->id();
          $swatches[$attribute_code][$value] = $data;

          break;
        }
      }
    }

    return $swatches;
  }

  /**
   * Get swatch attribute specific media entity roles.
   *
   * @param string $attribute_code
   *   Swatch attribute code.
   *
   * @return string
   *   Media role name. Default SWATCH_IMAGE_ROLE.
   */
  public function getSwatchAttributRole($attribute_code): string {
    $static = &drupal_static(__FUNCTION__, []);

    if (isset($static[$attribute_code])) {
      return $static[$attribute_code];
    }

    // Assigne default role to attribute.
    $static[$attribute_code] = self::SWATCH_IMAGE_ROLE;

    // Check if the attribute has a different swatch role.
    $swatches_roles = $this->configFactory->get('alshaya_acm_product.display_settings')->get('swatches_roles');
    if ($swatches_roles && isset($swatches_roles[$attribute_code])) {
      $static[$attribute_code] = $swatches_roles[$attribute_code];
    }

    return $static[$attribute_code];
  }

  /**
   * Get thumbnails of product along with all it's variants.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   variants image.
   */
  public function getAllVariantThumbnails(SKUInterface $sku): array {
    $plp_main_image = $variants_image = [];

    if ($sku->bundle() == 'simple') {
      $plugin = $sku->getPluginInstance();
      $sku = $plugin->getParentSku($sku);
    }

    $children = Configurable::getChildren($sku);
    $configurable_attributes = $this->skuManager->getConfigurableAttributes($sku);
    $duplicates = [];
    foreach ($children as $child) {
      $value = $this->skuManager->getPdpSwatchValue($child, $configurable_attributes);
      if (empty($value) || isset($duplicates[$value])) {
        continue;
      }

      $product_image = $this->getFirstImage($child);

      if (empty($product_image) || empty($product_image['drupal_uri'])) {
        continue;
      }

      $duplicates[$value] = 1;
      if (empty($plp_main_image)) {
        $plp_image = $this->skuImagesHelper->getSkuImage($product_image, SkuImagesHelper::STYLE_PRODUCT_LISTING);
        if (!empty($plp_image)) {
          $plp_main_image = $plp_image;
        }
      }

      $variant_image = $this->skuImagesHelper->getSkuImage(
        $product_image,
        SkuImagesHelper::STYLE_PRODUCT_LISTING,
        SkuImagesHelper::STYLE_PRODUCT_LISTING
      );
      if (!empty($variant_image)) {
        $variants_image[$child->id()][] = $variant_image;
      }
    }

    return [
      'mainImage' => $plp_main_image,
      'thumbnails' => $variants_image,
    ];

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

    if (!empty($media_item['drupal_uri'])) {
      $file_uri = $media_item['drupal_uri'];

      // Show original full image in the modal inside a draggable container.
      $original_image = file_create_url($file_uri);

      $image_small = ImageStyle::load($thumbnail_style)->buildUrl($file_uri);
      $image_zoom = ImageStyle::load($zoom_style)->buildUrl($file_uri);
      $image_medium = ImageStyle::load($slide_style)->buildUrl($file_uri);

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
    $return = [];
    $thumbnails = $media['thumbs'] ?? [];

    $main_image = $media['main'] ?? [];

    // Create our thumbnails to be rendered for zoom.
    foreach ($media['media_items']['images'] ?? [] as $media_item) {
      $this->getThumbnailImagesFromMedia($thumbnails, $media_item);
      if ($get_main_image && empty($main_image) && !empty($media_item['drupal_uri'])) {
        $thumbnail = end($thumbnails);
        $main_image = [
          'zoomurl' => $thumbnail['zoomurl'],
          'mediumurl' => $thumbnail['mediumurl'],
          'label' => $media_item['label'],
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
   * Get image roles to hide as array from config.
   *
   * @return array
   *   Roles to hide.
   */
  public function getImageRolesToHide() {
    $static = &drupal_static(__FUNCTION__, NULL);

    if ($static === NULL) {
      $roles_to_hide = $this->productDisplaySettings->get('media_roles_to_hide_in_gallery');
      $static = explode(',', $roles_to_hide);
    }

    return $static;
  }

  /**
   * Get all the swatch images with sku text as key.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   Parent SKU.
   *
   * @return array
   *   Swatches array.
   */
  public function getSwatches(SKUInterface $sku) {
    $swatches = $this->getSwatchesFromCache($sku);

    // We may have nothing for an SKU, we should not keep processing for it.
    // If value is not set, function returns NULL above so we check for array.
    if (is_array($swatches)) {
      return $swatches;
    }

    $swatches = [];
    $duplicates = [];
    $children = Configurable::getChildren($sku);
    $configurable_attributes = $this->skuManager->getConfigurableAttributes($sku);

    foreach ($children as $child) {
      $value = $this->skuManager->getPdpSwatchValue($child, $configurable_attributes);

      if (empty($value) || isset($duplicates[$value])) {
        continue;
      }

      $swatch_image = $this->getPdpSwatchImageUrl($child);

      if (empty($swatch_image)) {
        continue;
      }

      $duplicates[$value] = 1;
      $swatches[$child->id()] = [
        'url' => file_url_transform_relative($swatch_image),
      ];

      if ($this->productDisplaySettings->get('color_swatches_show_product_image') && $this->skuManager->isListingDisplayModeAggregated()) {
        $swatch_product_image = $child->getThumbnail();

        // If we have image for the product.
        if (!empty($swatch_product_image) && $swatch_product_image['file'] instanceof FileInterface) {
          $url = file_create_url($swatch_product_image['file']->getFileUri());
          $swatches[$child->id()]['product_url'] = file_url_transform_relative($url);
        }
      }
    }

    $this->setSwatchesToCache($sku, $swatches);

    return $swatches;
  }

  /**
   * Wrapper function to get swatches from cache and wakeup array.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   *
   * @return array|null
   *   Swatches array if found in cache or null.
   */
  private function getSwatchesFromCache(SKU $sku) {
    $swatches = $this->productCacheManager->get($sku, 'swatches');

    foreach ($swatches ?? [] as $index => $swatch) {
      foreach ($swatch as $key => $url) {
        $swatches[$index][$key] = self::getPublicDirectoryRelative() . $url;
      }
    }

    return $swatches;
  }

  /**
   * Wrapper function to set swatches to cache after removing duplicate data.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   * @param array $swatches
   *   Swatches.
   */
  private function setSwatchesToCache(SKU $sku, array $swatches) {
    foreach ($swatches ?? [] as $index => $swatch) {
      foreach ($swatch as $key => $url) {
        $swatches[$index][$key] = str_replace(self::getPublicDirectoryRelative(), '', $url);
      }
    }

    $this->productCacheManager->set($sku, 'swatches', $swatches, $sku->getCacheTags() ?? []);
  }

  /**
   * Get relative path to public directory.
   *
   * @return string
   *   Relative public directory path.
   */
  public static function getPublicDirectoryRelative() {
    static $public_dir;

    if (empty($public_dir)) {
      $public_dir = file_url_transform_relative(self::getPublicDirectory());
    }

    return $public_dir;
  }

  /**
   * Get absolute path to public directory.
   *
   * @return string
   *   Absolute public directory path.
   */
  public static function getPublicDirectory() {
    static $public_dir;

    if (empty($public_dir)) {
      $default_schema = \Drupal::config('system.file')->get('default_scheme');
      $public_dir = file_create_url($default_schema . '://');
    }

    return $public_dir;
  }

  /**
   * Wrapper function to get only the image urls for product.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   *
   * @return array
   *   Array containing absolute urls of images.
   */
  public function getMediaImages(SKU $sku): array {
    $media = $this->getProductMedia($sku, 'pdp');
    $images = [];

    foreach ($media['media_items']['images'] ?? [] as $item) {
      $images[] = file_create_url($item['drupal_uri']);
    }

    return $images;
  }

  /**
   * Processes styled images in media.
   *
   * @param array $media
   *   Array of product media to be processed.
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context - pdp/search/modal/teaser.
   *
   * @return array
   *   Media array containing styled images.
   */
  public function processMediaImageStyles(array $media, SKUInterface $sku, string $context) {
    if (empty($media['images'])) {
      return $media;
    }
    /** @var \Drupal\acq_sku\Entity\SKU $sku */
    $product_media = $this->getProductMedia($sku, $context);
    // Search image styles in product media using url and return styles.
    foreach ($media['images'] as $mid => $media_item) {
      $media['images'][$mid]['styles'] = [];
      $key = array_search(
        $media_item['url'],
        array_column(
          $product_media['media_items']['images'],
          'drupal_uri'
        )
      );
      // Check if images styles exists for the sku and return the urls.
      if (isset($key)) {
        $image = $product_media['media_items']['images'][$key];
        if (!empty($image['styles'])) {
          $media['images'][$mid]['styles'] = $image['styles'];
        }
        elseif (!empty($image['pims_image']['styles'])) {
          $media['images'][$mid]['styles'] = $image['pims_image']['styles'];
        }
      }
    }
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
    if (!empty($swatch_product_image) && $swatch_product_image['file'] instanceof FileInterface) {
      $url = file_create_url($swatch_product_image['file']->getFileUri());
      return $url;
    }

    return FALSE;
  }

}
