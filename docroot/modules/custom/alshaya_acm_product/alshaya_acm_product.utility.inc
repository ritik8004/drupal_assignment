<?php

/**
 * @file
 * Utility file to expose functions to get data related to product.
 */

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use Drupal\file\FileInterface;

/**
 * Utility function to return media files for a product.
 *
 * @param int $productId
 *   Product node id.
 * @param bool $first_image_only
 *   Flag to indicate if we want only the first image and not the whole array.
 *
 * @return array
 *   Array of media files.
 */
function alshaya_acm_product_get_product_media($productId, $first_image_only = FALSE) {
  // Load product from id.
  $product = Node::load($productId);

  if ($product instanceof Node) {
    // Get SKU from product.
    $sku_entity = $product->get('field_skus')
      ->first()
      ->get('entity')
      ->getValue();

    // Get the processed media data for the SKU.
    $media = alshaya_acm_product_get_sku_media($sku_entity, $first_image_only);

    // We return all media items.
    return $media;
  }

  // We didn't find any media files, returning null.
  return NULL;
}

/**
 * Utility function to return media files for a SKU.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 * @param bool $first_image_only
 *   Flag to indicate if we want only the first image and not the whole array.
 *
 * @return array
 *   Array of media files.
 */
function alshaya_acm_product_get_sku_media($sku, $first_image_only = FALSE) {
  $media = [];

  $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

  if (!($sku_entity instanceof SKU)) {
    return [];
  }

  if ($first_image_only) {
    return $sku_entity->getThumbnail();
  }

  return $sku_entity->getMedia();
}

/**
 * Get Image tag from media item array.
 *
 * @param array $media
 *   Media array containing image details.
 * @param string $image_style
 *   Image style to apply to the image.
 * @param string $rel_image_style
 *   For some sliders we may want full/big image url in rel.
 *
 * @return array
 *   Image build array.
 */
function alshaya_acm_product_get_sku_image(array $media, $image_style = '', $rel_image_style = '') {
  $image = [
    '#theme' => 'image_style',
    '#style_name' => $image_style,
    '#uri' => $media['file']->getFileUri(),
    '#title' => $media['label'],
    '#alt' => $media['label'],
  ];

  if ($rel_image_style) {
    $image['#attributes']['rel'] = ImageStyle::load($rel_image_style)->buildUrl($image['#uri']);
  }

  return $image;
}

/**
 * Utility function to return media files for a SKU.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 *
 * @return array
 *   Array of configurable field values.
 */
function alshaya_acm_product_get_sku_configurable_values($sku) {
  $configurableFieldValues = [];

  $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

  if ($sku_entity) {
    $fields = \Drupal::config('alshaya_acm_product.sku_base_fields')->get('fields');
    $configurableFields = array_filter($fields, function ($field) {
      return (bool) $field['configurable'];
    });

    foreach ($configurableFields as $key => $field) {
      $fieldKey = 'attr_' . $key;

      if ($sku_entity->get($fieldKey)->getString()) {
        $configurableFieldValues[$fieldKey] = [
          'label' => $sku_entity->get($fieldKey)->getFieldDefinition()->getLabel(),
          'value' => $sku_entity->get($fieldKey)->getString(),
        ];
      }
    }

  }
  return $configurableFieldValues;
}

/**
 * Utility function to get parent SKU for a configurable child sku.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 * @param string $langcode
 *   Language code.
 *
 * @return \Drupal\acq_sku\Entity\SKU
 *   Loaded SKU entity.
 */
function alshaya_acm_product_get_parent_sku_by_sku($sku, $langcode = '') {
  $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku, $langcode);

  // Additional check, can be removed post go UAT.
  if (empty($sku_entity)) {
    return NULL;
  }

  /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
  $plugin = $sku_entity->getPluginInstance();

  return $plugin->getParentSku($sku_entity);
}

/**
 * Helper function to get gallery and zoom for particular SKU.
 *
 * @param array $build
 *   Build array to add the gallery and zoom elements to.
 * @param \Drupal\acq_commerce\SKUInterface $sku_entity
 *   SKU Entity for which we want to display the media.
 */
function alshaya_acm_product_get_gallery(array &$build, SKUInterface $sku_entity) {
  \Drupal::moduleHandler()->loadInclude('alshaya_product_zoom', 'inc', 'alshaya_product_zoom.utility');

  // Fetch media.
  $media = alshaya_acm_product_get_sku_media($sku_entity);

  // Avoid notices and warnings in local.
  if (empty($media)) {
    return [];
  }

  $main_image = [];

  // Fetch settings.
  $settings = alshaya_product_zoom_cloudzoom_default_settings();
  $thumbnail_style = $settings['thumb_style'];
  $zoom_style = $settings['zoom_style'];
  $slide_style = $settings['slide_style'];

  // Create our thumbnails to be rendered for zoom.
  foreach ($media as $delta => $media_item) {
    if ($media_item['media_type'] == 'image') {

      $file_uri = $media_item['file']->getFileUri();

      // Show original full image in the modal inside a draggable container.
      $original_image = $media_item['file']->url();

      $image_small = ImageStyle::load($thumbnail_style)->buildUrl($file_uri);
      $image_zoom = ImageStyle::load($zoom_style)->buildUrl($file_uri);
      $image_medium = ImageStyle::load($slide_style)->buildUrl($file_uri);

      if (empty($main_image)) {
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
    elseif ($media_item['media_type'] == 'video') {
      if (strpos($media_item['video_provider'], 'youtube')) {
        $thumbnails[] = [
          'thumburl' => 'https://img.youtube.com/vi/' . alshaya_product_zoom_getyoutubeid($media_item['video_url']) . '/hqdefault.jpg',
          'url' => $media_item['video_url'],
          'type' => 'youtube',
          // @TODO: should this be config?
          'width' => 81,
          // @TODO: should this be config?
          'height' => 81,
        ];
      }
      else {
        $thumbnails[] = [
          'url' => $media_item['video_url'],
          'type' => 'vimeo',
          // @TODO: should this be config?
          'width' => 81,
          // @TODO: should this be config?
          'height' => 81,
        ];
      }
    }
  }

  // @TODO: should this be config?
  $pager_flag = count($thumbnails) > 5 ? 'pager-yes' : 'pager-no';

  $build['gallery'] = [
    '#type' => 'container',
    '#attributes' => [
      'class' => ['gallery-wrapper'],
    ],
  ];

  $build['gallery']['product_zoom'] = [
    '#theme' => 'product_zoom',
    '#mainImage' => $main_image,
    '#thumbnails' => $thumbnails,
    '#pager_flag' => $pager_flag,
    '#properties' => alshaya_product_zoom_get_rel_cloudzoom($settings),
    '#attached' => [
      'library' => [
        'alshaya_product_zoom/product.cloud_zoom',
      ],
    ],
  ];

}

/**
 * Utility function to get parent node of the sku.
 *
 * @param mixed $sku
 *   SKU name or full sku object.
 * @param string $langcode
 *   Language code.
 *
 * @return object
 *   Loaded node object.
 */
function alshaya_acm_product_get_display_node($sku, $langcode = '') {
  $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku, $langcode);

  /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
  $plugin = $sku_entity->getPluginInstance();

  return $plugin->getDisplayNode($sku_entity);
}

/**
 * Helper function to get the product display image.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 * @param string $image_style
 *   Image style to apply to the image.
 *
 * @return array
 *   Return string of uri or Null if not found.
 */
function alshaya_acm_get_product_display_image($sku, $image_style = '') {
  // Load the first image.
  $media_image = alshaya_acm_product_get_sku_media($sku, TRUE);

  // If we have image for the product.
  if (!empty($media_image)) {
    if ($media_image['file'] instanceof FileInterface) {
      return alshaya_acm_product_get_sku_image($media_image, $image_style);
    }
  }
  // If no image found, check once for parent product as well.
  elseif ($parent_sku = alshaya_acm_product_get_parent_sku_by_sku($sku)) {
    return alshaya_acm_get_product_display_image($parent_sku, $image_style);
  }

  return [];
}

/**
 * Get the url of the image of SKU.
 *
 * @param \Drupal\acq_sku\Entity\SKU $sku
 *   SKU entity object.
 *
 * @return string
 *   Url of the image.
 */
function _alshaya_acm_get_product_display_image_url(SKU $sku) {
  // Load the first image.
  $media_image = alshaya_acm_product_get_sku_media($sku, TRUE);

  // If we have image for the product.
  if (!empty($media_image)) {
    if ($media_image['file'] instanceof FileInterface) {
      $uri = $media_image['file']->getFileUri();
      return file_create_url($uri);
    }
  }

  return '';
}

/**
 * Helper function to get child skus for a configurable sku from tree.
 *
 * @param array $tree
 *   Tree containing configurable options and SKU objects.
 * @param array $configurables
 *   Configurable values array.
 *
 * @return null|SKU
 *   Null or SKU instance.
 */
function alshaya_acm_product_get_selected_option_sku(array $tree, array $configurables) {
  $tree_pointer = &$tree['options'];

  foreach ($configurables as $key => $value) {
    if (empty($value)) {
      continue;
    }

    // Move the tree pointer if the selection is valid.
    if (isset($tree_pointer["$key:$value"])) {
      return $tree_pointer["$key:$value"];
    }
  }

  return NULL;
}

/**
 * Utility function to check if product is available for home delivery.
 *
 * @param mixed $sku
 *   SKU string or full object.
 *
 * @return bool
 *   Flag if the product is available for click and collect or not.
 */
function alshaya_acm_product_available_home_delivery($sku) {
  // Check MMCSP-903 comments for more details.
  return TRUE;
}

/**
 * Utility function to check if product is available for click and collect.
 *
 * @param mixed $sku
 *   SKU string or full object.
 *
 * @return bool
 *   Flag if the product is available for click and collect or not.
 */
function alshaya_acm_product_available_click_collect($sku) {
  $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

  $sts = (int) $sku_entity->get('attr_ship_to_store')->getString();
  $rnc = (int) $sku_entity->get('attr_reserve_and_collect')->getString();

  // Product could be either available for ship to store or for reserve and
  // collect. In both cases click and collect option will be considered as
  // available.
  return ($sts || $rnc);
}

/**
 * Utility function to get brand logo image.
 *
 * @param mixed $sku
 *   SKU string or full object.
 *
 * @return array
 *   Image build array.
 */
function alshaya_acm_product_get_brand_logo($sku) {
  $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

  $title = $sku_entity->get('attr_brand_logo')->getString();

  $cid = 'brand_logo:' . Html::cleanCssIdentifier($title);

  if ($cache = \Drupal::cache('data')->get($cid)) {
    $image = $cache->data;
  }
  else {
    // Preparing args for all info/error messages.
    $args = ['@title' => $title, '@sku_id' => $sku_entity->id()];

    $host = \Drupal::config('alshaya_api.settings')->get('magento_host');
    $media_path = \Drupal::config('alshaya_acm_product.settings')->get('brand_logo_base_path');

    $directory = $media_path;

    // Prepare file name by replacing spaces with underscores and lower case.
    $file_name = strtolower(str_replace(' ', '_', $title));

    for ($i = 0; $i < 2 && $i <= strlen($title); $i++) {
      $directory .= '/' . substr($file_name, $i, 1);
    }

    $directory_uri = 'public://brand-logo/' . $directory;
    $image_uri = $directory_uri . '/' . $file_name . '.jpeg';
    $image_url = $host . '/' . $directory . '/' . $file_name . '.jpeg';

    $file_storage = \Drupal::entityTypeManager()->getStorage('file');

    if ($files = $file_storage->loadByProperties(['uri' => $image_uri])) {
      $file = reset($files);
    }
    else {
      // Download the file contents.
      $file_data = file_get_contents($image_url);

      // Check to ensure errors like 404, 403, etc. are catched and empty file
      // not saved in SKU.
      if (empty($file_data)) {
        \Drupal::logger('alshaya_acm_product')->error('Failed to download brand logo of title "@title" for SKU id @sku_id.', $args);
      }

      // Get the file name.
      $file_name = basename($image_url);

      // Prepare the directory.
      file_prepare_directory($directory_uri, FILE_CREATE_DIRECTORY);

      // Save the file as file entity.
      /** @var \Drupal\file\Entity\File $file */
      $file = file_save_data($file_data, $image_uri, FILE_EXISTS_REPLACE);

      if (empty($file)) {
        \Drupal::logger('alshaya_acm_product')->error('Failed to save brand logo of title "@title" for SKU id @sku_id.', $args);
      }
    }

    if ($file) {
      $image = [
        '#theme' => 'image',
        '#uri' => $file->getFileUri(),
        '#title' => $title,
        '#alt' => $title,
      ];

      $expire = CacheBackendInterface::CACHE_PERMANENT;
    }
    else {
      $image = [];

      // We cache this for 30 minutes to allow admins to correct the situation.
      $expire = \Drupal::time()->getRequestTime() + (60 * 30);
    }

    \Drupal::cache('data')->set($cid, $image, $expire);
  }

  return $image;
}

/**
 * Helper function to fetch sku from entity id rather than loading the SKU.
 *
 * @param int $sku_entity_id
 *   Entity id of the Sku item.
 *
 * @return string
 *   Sku Id of the item.
 */
function alshaya_acm_product_get_sku_by_entity_id($sku_entity_id) {
  $query = \Drupal::database()->select('acq_sku_field_data', 'asfd')
    ->fields('asfd', ['sku'])
    ->condition('id', $sku_entity_id)
    ->range(0, 1);

  return $query->execute()->fetchField();
}
