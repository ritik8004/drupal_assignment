<?php

namespace Drupal\alshaya_media_assets\Services;

use Drupal\acq_sku\Entity\SKU;

/**
 * Sku Media Assets Class.
 */
interface SkuAssetManagerInterface {

  /**
   * Constant to denote that the current asset has no angle data associated.
   */
  const LP_DEFAULT_ANGLE = 'NO_ANGLE';

  /**
   * Constant for RGB swatch display type.
   */
  const LP_SWATCH_RGB = 'RGB';

  /**
   * Get assets for SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   *
   * @return array
   *   Get assets for SKU.
   *
   * @throws \Exception
   */
  public function getAssets(SKU $sku);

  /**
   * Utility function to construct CDN url for the asset.
   *
   * @param mixed $sku
   *   SKU text or full entity object.
   * @param string $page_type
   *   Page on which the asset needs to be rendered.
   * @param array $avoid_assets
   *   (optional) Array of AssetId to avoid.
   *
   * @return array
   *   Array of urls to sku assets.
   */
  public function getSkuAssets($sku, $page_type, array $avoid_assets = []);

  /**
   * Helper function to get swatch data.
   *
   * @param mixed $sku
   *   SKU text or full entity object.
   *
   * @return string
   *   Url to sku swatch.
   */
  public function getSkuSwatch($sku);

  /**
   * Helper function to sort based on angles.
   *
   * @param string $sku
   *   SKU code for which the assets needs to be sorted on angles.
   * @param string $page_type
   *   Page on which the asset needs to be rendered.
   * @param array $assets
   *   Array of mixed asset types.
   *
   * @return array
   *   Array of assets sorted by their asset types & angles.
   */
  public function sortSkuAssets($sku, $page_type, array $assets);

  /**
   * Helper function to filter out specific asset types from a list.
   *
   * @param array $assets
   *   Array of assets with mixed asset types.
   * @param string $asset_type
   *   Asset type that needs to be filtered out.
   *
   * @return array
   *   Array of assets matching the asset type.
   */
  public function filterSkuAssetType(array $assets, $asset_type);

  /**
   * Helper function to pull child sku assets.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Parent sku for which we pulling child assets.
   * @param string $context
   *   Page on which the asset needs to be rendered.
   * @param array $avoid_assets
   *   (optional) Array of AssetId to avoid.
   *
   * @return array
   *   Array of sku child assets.
   */
  public function getChildSkuAssets(SKU $sku, $context, array $avoid_assets = []);

  /**
   * Helper function to get assets for a SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Parent Sku.
   * @param string $page_type
   *   Type of page.
   *
   * @return array|assets
   *   Array of assets for the SKU.
   */
  public function getAssetsForSku(SKU $sku, $page_type);

  /**
   * Helper function to get asset type.
   *
   * @param array $asset
   *   Array of asset details.
   *
   * @return string
   *   Asset type (video/image).
   */
  public function getAssetType(array $asset);

  /**
   * Helper function to fetch list of color options supported by a parent SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Parent sku.
   *
   * @return array
   *   Array of RGB color values keyed by article_castor_id.
   */
  public function getColorsForSku(SKU $sku);

  /**
   * Helper function to fetch swatch type for the sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Sku for which swatch type needs to be fetched.
   *
   * @return string
   *   Swatch type for the sku.
   */
  public function getSkuSwatchType(SKU $sku);

}
