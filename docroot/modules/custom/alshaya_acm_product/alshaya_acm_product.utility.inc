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

/**
 * Utility function to return media files for a product.
 *
 * @param int $productId
 *   Product node id.
 *
 * @return array
 *   Array of media files.
 */
function alshaya_acm_product_get_product_media($productId) {
  // Load product from id.
  $product = Node::load($productId);

  if ($product instanceof Node) {
    // Get SKU from product.
    $skuEntity = $product->get('field_skus')
      ->first()
      ->get('entity')
      ->getValue();

    return alshaya_acm_product_get_sku_media($skuEntity);
  }
  return NULL;
}

/**
 * Utility function to return media files for a SKU.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 *
 * @return array
 *   Array of media files.
 */
function alshaya_acm_product_get_sku_media($sku) {
  $media = [];

  $skuEntity = is_object($sku) ? $sku : SKU::loadFromSku($sku);

  if ($skuEntity instanceof SKU) {
    // For now we load only the image field.
    $imageField = $skuEntity->get('attr_image')->getValue();

    // We will return the array of File objects.
    foreach ($imageField as $image) {
      $media[] = File::load($image['target_id']);
    }
  }

  return $media;
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

  $skuEntity = is_object($sku) ? $sku : SKU::loadFromSku($sku);

  if ($skuEntity instanceof SKU) {
    $fields = \Drupal::config('alshaya_acm_product.sku_base_fields')
      ->get('fields');
    $configurableFields = array_filter($fields, function ($field) {
      return (bool) $field['configurable'];
    });

    foreach ($configurableFields as $key => $field) {
      $fieldKey = 'attr_' . $key;

      $configurableFieldValues[$fieldKey] = [
        'label' => $skuEntity->get($fieldKey)->getFieldDefinition()->getLabel(),
        'value' => $skuEntity->get($fieldKey)->getString(),
      ];
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

  if (count($ids) != 1) {
    \Drupal::logger('alshaya_acm_product')->error(
      'SKU @sku has no or multiple parent SKUs.',
      ['@sku' => $sku]
    );

    throw new RuntimeException('No or multiple parent SKUs found');
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
 * @param \Drupal\acq_commerce\SKUInterface $skuEntity
 *   SKU Entity for which we want to display the media.
 */
function alshaya_acm_product_get_gallery(array &$build, SKUInterface $skuEntity) {
  \Drupal::moduleHandler()->loadInclude('alshaya_product_zoom', 'inc', 'alshaya_product_zoom.utility');

  // Fetch media.
  $media = alshaya_acm_product_get_sku_media($skuEntity);

  // Fetch settings.
  $settings = alshaya_product_zoom_cloudzoom_default_settings();
  $thumbnail_style = $settings['thumb_style'];
  $zoom_style = $settings['zoom_style'];
  $slide_style = $settings['slide_style'];

  // Create our thumbnails to be rendered for zoom.
  foreach ($media as $delta => $image) {
    $file_uri = $image->getFileUri();
    // Show original full image in the modal inside a draggable container.
    $original_image = $image->url();
    if ($delta == 0) {
      $imageZoom = ImageStyle::load($zoom_style)->buildUrl($file_uri);
      $imageMedium = ImageStyle::load($slide_style)->buildUrl($file_uri);
      $main_image = [
        'zoomurl' => $imageZoom,
        'mediumurl' => $imageMedium,
      ];
    }
    $imageSmall = ImageStyle::load($thumbnail_style)->buildUrl($file_uri);
    $imageZoom = ImageStyle::load($zoom_style)->buildUrl($file_uri);
    $imageMedium = ImageStyle::load($slide_style)->buildUrl($file_uri);
    $thumbnails[] = [
      'thumburl' => $imageSmall,
      'mediumurl' => $imageMedium,
      'zoomurl' => $imageZoom,
      'fullurl' => $original_image,
      'type' => 'image',
    ];
  }
  // @TODO: Get video data.
  $video = [];
  $videos = [
    'https://www.youtube.com/embed/eKG08z85DtY',
    'https://player.vimeo.com/video/1084537',
  ];
  foreach ($videos as $video) {
    if (strpos($video, 'youtube')) {
      $thumbnails[] = [
        'thumburl' => 'https://img.youtube.com/vi/' . alshaya_product_zoom_getyoutubeid($video) . '/hqdefault.jpg',
        'url' => $video,
        'type' => 'youtube',
        'width' => 81,
        'height' => 81,
      ];
    }
    else {
      $thumbnails[] = [
        'url' => $video,
        'type' => 'vimeo',
        'width' => 81,
        'height' => 81,
      ];
    }
  }

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
    '#properties' => alshaya_product_zoom_get_rel_cloudzoom($settings),
    '#attached' => [
      'library' => [
        'alshaya_product_zoom/product.cloud_zoom',
      ],
    ],
  ];

  $build['gallery']['product_zoom_gallery'] = [
    '#theme' => 'product_zoom_gallery',
    '#thumbnails' => $thumbnails,
    '#attached' => [
      'library' => [
        'alshaya_product_zoom/product.product_zoom_gallery',
      ],
    ],
  ];
}
