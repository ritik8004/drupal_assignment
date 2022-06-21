<?php

namespace Drupal\alshaya_pims;

use Drupal\alshaya_acm_product\SkuImagesHelper;

/**
 * Class Sku images helper pims.
 *
 * @package Drupal\alshaya_pims
 */
class SkuImagesHelperPims extends SkuImagesHelper {

  /**
   * Inner service SkuImagesHelper.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesHelper
   */
  protected $innerService;

  /**
   * SkuImagesHelperPims constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuImagesHelper $sku_images_helper
   *   Sku images helper inner service.
   */
  public function __construct(SkuImagesHelper $sku_images_helper) {
    $this->innerService = $sku_images_helper;
  }

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

    $image = [
      '#theme' => 'image',
      '#uri' => $this->getImageStyleUrl($media, $style_name),
      '#attributes' => [
        'src' => $this->getImageStyleUrl($media, $style_name),
        'title' => $media['label'],
        'alt' => $media['label'],
      ],
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
   * @return mixed|string
   *   Image url.
   */
  public function getImageStyleUrl(array $media, string $style_name) {
    if (isset($media['pims_image']['styles'])) {
      $media = $media['pims_image'];
    }

    if (!empty($media['styles']) && !empty($media['styles'][$style_name])) {
      return $media['styles'][$style_name];
    }
    return '';
  }

}
