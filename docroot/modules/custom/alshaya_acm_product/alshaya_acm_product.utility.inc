<?php

/**
 * @file
 * Utility file to expose functions to get data related to product.
 */

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
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

  $sku_entity = is_object($sku) ? $sku : SKU::loadFromSku($sku);

  if (!($sku_entity instanceof SKU)) {
    return [];
  }

  if ($media_data = $sku_entity->get('attr_media')->getString()) {
    $update_sku = FALSE;

    $media_data_full = unserialize($media_data);

    if (empty($media_data_full)) {
      return [];
    }

    // @TODO: Remove this hard coded fix after getting answer why we have empty
    // second array index and why all media come in first array index.
    $media_data = reset($media_data_full);

    foreach ($media_data as &$data) {
      if ($data['media_type'] == 'image') {
        if (empty($data['fid'])) {
          try {
            // Prepare the File object when we access it the first time.
            $data['fid'] = alshaya_acm_product_download_sku_media_image($data, $sku_entity);
            $update_sku = TRUE;
          }
          catch (\Exception $e) {
            \Drupal::logger('alshaya_acm_product')->error($e->getMessage());
            continue;
          }
        }

        $data['file'] = File::load($data['fid']);

        if (empty($data['label'])) {
          $data['label'] = $sku_entity->label();
        }
      }

      $media[$data['position']] = $data;
    }

    if ($update_sku) {
      // @TODO: Remove this hard coded fix after getting answer why we have
      // empty second array index and why all media come in first array index.
      $media_data_full[0] = $media_data;
      $sku_entity->get('attr_media')->setValue(serialize($media_data_full));
      $sku_entity->save();
    }
  }

  // Sort them by position again to ensure it works everytime.
  ksort($media);

  if ($first_image_only) {
    // We loop through all the media items and return the first image.
    foreach ($media as $media_item) {
      if ($media_item['media_type'] == 'image') {
        return $media_item;
      }
    }

    // We didn't find any image media so returning null.
    return NULL;
  }

  return $media;
}

/**
 * Function to save image file into public dir.
 *
 * @param array $data
 *   File data.
 * @param \Drupal\acq_sku\Entity\SKU $sku
 *   SKU entity.
 *
 * @return int
 *   File id.
 */
function alshaya_acm_product_download_sku_media_image(array $data, SKU $sku) {
  // Preparing args for all info/error messages.
  $args = ['@file' => $data['file'], '@sku_id' => $sku->id()];

  // Download the file contents.
  $file_data = file_get_contents($data['file']);

  // Check to ensure errors like 404, 403, etc. are catched and empty file
  // not saved in SKU.
  if (empty($file_data)) {
    throw new \Exception(new FormattableMarkup('Failed to download file "@file" for SKU id @sku_id.', $args));
  }

  // Get the path part in the url, remove hostname.
  $path = parse_url($data['file'], PHP_URL_PATH);

  // Remove slashes from start and end.
  $path = trim($path, '/');

  // Get the file name.
  $file_name = basename($path);

  // Prepare the directory path.
  $directory = 'public://acm/' . str_replace('/' . $file_name, '', $path);

  // Prepare the directory.
  file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

  // Save the file as file entity.
  /** @var \Drupal\file\Entity\File $file */
  if ($file = file_save_data($file_data, $directory . '/' . $file_name, FILE_EXISTS_REPLACE)) {
    return $file->id();
  }
  else {
    throw new \Exception(new FormattableMarkup('Failed to save file "@file" for SKU id @sku_id.', $args));
  }
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

  $sku_entity = is_object($sku) ? $sku : SKU::loadFromSku($sku);

  if ($sku_entity instanceof SKU) {
    $fields = \Drupal::config('alshaya_acm_product.sku_base_fields')
      ->get('fields');
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
 *
 * @return object
 *   Loaded SKU entity.
 */
function alshaya_acm_product_get_parent_sku_by_sku($sku) {
  $sku = is_object($sku) ? $sku->getSKU() : $sku;

  // Execute the query and get all SKU which have current SKU in
  // field_configured_skus.
  $query = \Drupal::entityQuery('acq_sku');
  $query->condition('field_configured_skus', $sku);
  $ids = $query->execute();

  // Not a configurable product.
  if (count($ids) === 0) {
    return NULL;
  }
  // Some issue in DATA.
  elseif (count($ids) > 1) {
    \Drupal::logger('alshaya_acm_product')->error(
      'SKU @sku has no or multiple parent SKUs.',
      ['@sku' => $sku]
    );

    throw new RuntimeException('Multiple parent SKUs found');
  }

  // Get the first id.
  $parentSkuId = array_shift($ids);

  // Return fully load SKU.
  return SKU::load($parentSkuId);
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

      if (empty($main_image)) {
        $image_zoom = ImageStyle::load($zoom_style)->buildUrl($file_uri);
        $image_medium = ImageStyle::load($slide_style)->buildUrl($file_uri);
        $main_image = [
          'zoomurl' => $image_zoom,
          'mediumurl' => $image_medium,
        ];
      }

      $image_small = ImageStyle::load($thumbnail_style)->buildUrl($file_uri);
      $image_zoom = ImageStyle::load($zoom_style)->buildUrl($file_uri);
      $image_medium = ImageStyle::load($slide_style)->buildUrl($file_uri);

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
 *
 * @return object
 *   Loaded node object.
 */
function alshaya_acm_product_get_display_node($sku) {
  $sku = is_object($sku) ? $sku->getSKU() : $sku;

  $query = \Drupal::entityQuery('node')
    ->condition('type', 'acq_product')
    ->condition('field_skus', $sku)
    ->range(0, 1);

  $result = $query->execute();
  $nid = reset($result);

  return Node::load($nid);
}

/**
 * Helper function to get the product display image.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 *
 * @return null|string
 *   Return string of uri or Null if not found.
 */
function alshaya_acm_get_product_display_image($sku) {
  // Load sku from item_id that we have in $item.
  $media = alshaya_acm_product_get_sku_media($sku);

  // If we have image for the product.
  if (!empty($media)) {
    $image = array_shift($media);
    if (is_object($image['file']) && $image['file'] instanceof FileInterface) {
      $file_uri = $image['file']->getFileUri();
      return $file_uri;
    }
  }
  // Sometimes sku created for size only, such sku don't have any image. only
  // parent sku has image. In such cases get the image of parent sku.
  elseif ($parent_sku = alshaya_acm_product_get_parent_sku_by_sku($sku)) {
    return alshaya_acm_get_product_display_image($parent_sku);
  }

  return NULL;
}
