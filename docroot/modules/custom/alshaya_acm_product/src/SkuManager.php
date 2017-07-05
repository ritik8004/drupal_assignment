<?php

namespace Drupal\alshaya_acm_product;

use Drupal\acq_sku\Entity\SKU;
use Drupal\image\Entity\ImageStyle;

/**
 * Class SkuManager.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuManager {

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
  public function getSkuMedia($sku, $first_image_only = FALSE) {
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
  public function getSkuImage(array $media, $image_style = '', $rel_image_style = '') {
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
   * Helper function to add price, final_price and discount info in build array.
   *
   * @param array $build
   *   Build array to modify.
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   SKU entity to use for getting price.
   */
  public function buildPrice(array &$build, SKU $sku_entity) {
    // Get the price, discounted price and discount.
    $build['price'] = $build['final_price'] = $build['discount'] = [];

    if ($price = (float) $sku_entity->get('price')->getString()) {
      $build['price'] = [
        '#theme' => 'acq_commerce_price',
        '#price' => $price,
      ];

      // Get the discounted price.
      if ($final_price = (float) $sku_entity->get('final_price')->getString()) {
        // Final price could be same as price, we dont need to show discount.
        if ($final_price >= $price) {
          return;
        }

        $build['final_price'] = [
          '#theme' => 'acq_commerce_price',
          '#price' => $final_price,
        ];

        // Get discount if discounted price available.
        $discount = floor((($price - $final_price) * 100) / $price);
        $build['discount'] = [
          '#markup' => t('Save @discount', ['@discount' => $discount . '%']),
        ];
      }
    }
    elseif ($final_price = (float) $sku_entity->get('final_price')->getString()) {
      $build['price'] = [
        '#theme' => 'acq_commerce_price',
        '#price' => $final_price,
      ];
    }
  }

}
