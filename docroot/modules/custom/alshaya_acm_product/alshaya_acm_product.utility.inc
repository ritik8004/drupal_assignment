<?php

/**
 * @file
 * Utility file to expose functions to get data related to product.
 */

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
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

  $sku_entity = is_object($sku) ? $sku : SKU::loadFromSku($sku);

  if (!($sku_entity instanceof SKU)) {
    return [];
  }

  if ($first_image_only) {
    return $sku_entity->getThumbnail();
  }

  return $sku_entity->getMedia();
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
  // Load the first image.
  $media_image = alshaya_acm_product_get_sku_media($sku, TRUE);

  // If we have image for the product.
  if (!empty($media_image)) {
    if (is_object($media_image['file']) && $media_image['file'] instanceof FileInterface) {
      $file_uri = $media_image['file']->getFileUri();
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
