<?php

namespace Drupal\alshaya_pims_assets\Services;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_media_assets\Services\SkuAssetManager;

/**
 * Sku Asset Manager Class override for PIMS.
 */
class PimsSkuAssetManager extends SkuAssetManager {

  /**
   * {@inheritDoc}
   */
  public function getAssets(SKU $sku) {
    // Suppress the unserialize class warning.
    // @codingStandardsIgnoreLine
    $assets = unserialize($sku->get('attr_assets')->getString());
    if (!is_array($assets) || empty($assets)) {
      return [];
    }

    foreach ($assets as $index => &$asset) {
      // Sanity check, we always need asset id.
      if (empty($asset['Data']['AssetId'])) {
        unset($assets[$index]);
        continue;
      }

      if (isset($asset['pims_image']['url']) && $this->validateFileExtension($sku->getSku(), $asset['pims_image']['url'])) {
        $asset['drupal_uri'] = $asset['pims_image']['url'];
      }
      else {
        unset($assets[$index]);
      }
    }

    return $assets;
  }

  /**
   * Helper function to get swatch data.
   *
   * @param mixed $sku
   *   SKU text or full entity object.
   *
   * @return string
   *   Url to sku swatch.
   */
  public function getSkuSwatch($sku) {
    $swatch = [];
    $skuEntity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);
    $assets_data = $skuEntity->get('attr_assets')->getValue();
    $sku_asset_type = $this->getImageSettings()->get('swatch_asset_type');

    if ($assets_data && isset($assets_data[0], $assets_data[0]['value'])) {
      // Suppress the unserialize class warning.
      // @codingStandardsIgnoreLine
      $unserialized_assets = unserialize($assets_data[0]['value']);

      foreach ($unserialized_assets as $assets) {
        if ($assets['Data']['AssetType'] === $sku_asset_type) {
          $swatch['url'] = $assets['pims_image']['url'] ?? NULL;
          $swatch['type'] = $assets['sortAssetType'] ?? NULL;
        }
      }
    }

    return $swatch;
  }

}
