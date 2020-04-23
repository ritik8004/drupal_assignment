<?php

namespace Drupal\alshaya_acm_product;

/**
 * Class SkuVideosManager.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuVideosManager {

  /**
   * Helper function to get asset type.
   *
   * @param array $asset
   *   Array of asset details.
   *
   * @return string
   *   Asset type (video/image).
   */
  public function getAssetType(array $asset) {
    if (strpos($asset['Data']['AssetType'], 'MovingMedia') !== FALSE) {
      return 'video';
    }
    elseif (strpos($asset['Data']['AssetType'], 'StillMediaComponents') !== FALSE) {
      return 'image';
    }
  }

}
