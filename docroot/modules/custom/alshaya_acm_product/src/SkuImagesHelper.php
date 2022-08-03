<?php

namespace Drupal\alshaya_acm_product;

use Drupal\image\Entity\ImageStyle;

/**
 * Class Sku images helper.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuImagesHelper {
  // Image Styles.
  public const STYLE_PRODUCT_LISTING = 'product_listing';
  public const STYLE_PRODUCT_SLIDE = 'product_zoom_medium_606x504';
  public const STYLE_PRODUCT_ZOOM = 'product_zoom_large_800x800';
  public const STYLE_PRODUCT_THUMBNAIL = 'pdp_gallery_thumbnail';
  public const STYLE_PRODUCT_TEASER = 'product_teaser';
  public const STYLE_CART_THUMBNAIL = 'cart_thumbnail';

  /**
   * Get Image tag with optional rel.
   *
   * @param array $media
   *   Media array with uri.
   * @param string $style_name
   *   Image style to apply to the image.
   * @param string $rel_image_style
   *   For some sliders we may want full/big image url in rel.
   *
   * @return array
   *   Image build array.
   */
  public function getSkuImage(array $media,
                              string $style_name,
                              string $rel_image_style = '') {
    if (empty($media['drupal_uri'])) {
      return [];
    }

    $image = [
      '#theme' => 'image_style',
      '#style_name' => $style_name,
      '#uri' => $media['drupal_uri'],
      '#title' => $media['label'],
      '#alt' => $media['label'],
    ];

    if ($rel_image_style) {
      $image['#attributes']['rel'] = $this->getImageStyleUrl($media, $rel_image_style);
    }

    return $image;
  }

  /**
   * Get image style url.
   *
   * @param array $media
   *   Media array.
   * @param string $style_name
   *   Image style name.
   *
   * @return \Drupal\Core\GeneratedUrl|false|string
   *   Image style url.
   */
  public function getImageStyleUrl(array $media, string $style_name) {
    if (empty($media['drupal_uri'])) {
      return '';
    }

    return ImageStyle::load($style_name)->buildUrl($media['drupal_uri']);
  }

}
