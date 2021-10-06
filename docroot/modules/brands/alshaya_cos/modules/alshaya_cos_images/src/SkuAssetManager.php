<?php

namespace Drupal\alshaya_cos_images;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Config\ConfigFactory;

/**
 * Sku Asset Manager Class.
 */
class SkuAssetManager {

  /**
   * Constant to denote that the current asset has no angle data associated.
   */
  const LP_DEFAULT_ANGLE = 'NO_ANGLE';

  /**
   * The Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * SkuAssetManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config Factory service.
   */
  public function __construct(ConfigFactory $configFactory) {
    $this->configFactory = $configFactory;
  }

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
  public function getAssets(SKU $sku) {
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
      $asset['drupal_uri'] = $asset['pims_image']['url'];
    }

    return $assets;
  }

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
  public function getSkuAssets($sku, $page_type, array $avoid_assets = []) {
    $skuEntity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);
    $sku = $skuEntity->getSku();

    $assets_data = $skuEntity->get('attr_assets')->getValue();

    if ($assets_data && isset($assets_data[0], $assets_data[0]['value'])) {
      $unserialized_assets = unserialize($assets_data[0]['value']);
      if ($unserialized_assets) {
        $assets = $this->sortSkuAssets($sku, $page_type, $unserialized_assets);
      }
    }

    if (empty($assets)) {
      return [];
    }

    $media = $this->getAssets($skuEntity);

    $return = [];
    foreach ($assets as $asset) {
      $asset_id = $asset['Data']['AssetId'];
      if (isset($media[$asset_id]) && !in_array($asset_id, $avoid_assets)) {
        $return[] = $media[$asset_id];
      }
    }

    return $return;
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

    if ($assets_data && isset($assets_data[0], $assets_data[0]['value'])) {
      $unserialized_assets = unserialize($assets_data[0]['value']);
      foreach ($unserialized_assets as $assets) {
        if ($assets['Data']['AssetType'] === 'StillMedia/Fabricswatch') {
          $swatch['url'] = $assets['Data']['PublicAssetService'];
          $swatch['type'] = $assets['sortAssetType'];
        }
      }
    }

    return $swatch;
  }

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
  public function sortSkuAssets($sku, $page_type, array $assets) {
    $alshaya_cos_images_config = $this->configFactory->get('alshaya_cos_images.settings');
    // Fetch weights of asset types based on the pagetype.
    $sku_asset_type_weights = $alshaya_cos_images_config->get('weights')[$page_type];
    // Fetch angle config.
    $sort_angle_weights = $alshaya_cos_images_config->get('weights')['angle'];

    // Create multi-dimensional array of assets keyed by their asset type.
    if (!empty($assets)) {
      $grouped_assets = [];
      foreach ($sku_asset_type_weights as $asset_type => $weight) {
        $grouped_assets[$asset_type] = $this->filterSkuAssetType($assets, $asset_type);
      }
      // Sort items based on the angle config.
      foreach ($grouped_assets as $key => $asset) {
        if (!empty($asset)) {
          $sort_angle_weights = array_flip($sort_angle_weights);
          uasort($asset, function ($a, $b) use ($key) {
            // Different rules for LookBook and reset.
            if ($key != 'Lookbook') {
              // For non-lookbook first check packaging in/out.
              // packaging=false first.
              // IsMultiPack didn't help, we check Facing now.
              $a_packaging = isset($a['Data']['Angle']['Packaging'])
                ? (float) $a['Data']['Angle']['Packaging']
                : NULL;
              $b_packaging = isset($b['Data']['Angle']['Packaging'])
                ? (float) $b['Data']['Angle']['Packaging']
                : NULL;

              if ($a_packaging != $b_packaging) {
                return $a_packaging === 'true' ? -1 : 1;
              }
            }

            $a_multi_pack = isset($a['Data']['IsMultiPack'])
              ? $a['Data']['IsMultiPack']
              : NULL;
            $b_multi_pack = isset($b['Data']['IsMultiPack'])
              ? $b['Data']['IsMultiPack']
              : NULL;
            if ($a_multi_pack != $b_multi_pack) {
              return $a_multi_pack === 'true' ? -1 : 1;
            }

            // IsMultiPack didn't help, we check Facing now.
            $a_facing = isset($a['Data']['Angle']['Facing'])
              ? (float) $a['Data']['Angle']['Facing']
              : 0;
            $b_facing = isset($b['Data']['Angle']['Facing'])
              ? (float) $b['Data']['Angle']['Facing']
              : 0;

            if ($a_facing != $b_facing) {
              return $a_facing - $b_facing < 0 ? -1 : 1;
            }

            // Finally sort by Number.
            $a_number = isset($a['Data']['Angle']['Number'])
              ? (float) $a['Data']['Angle']['Number']
              : 0;

            $b_number = isset($b['Data']['Angle']['Number'])
              ? (float) $b['Data']['Angle']['Number']
              : 0;

            if ($a_number != $b_number) {
              return $a_number - $b_number < 0 ? -1 : 1;
            }

            return 0;
          });
          $grouped_assets[$key] = $asset;
        }
        else {
          unset($grouped_assets[$key]);
        }
      }
      // Flatten the assets array.
      $flattened_assets = [];
      foreach ($grouped_assets as $assets) {
        $flattened_assets = array_merge($flattened_assets, $assets);
      }

      return $flattened_assets;
    }

    return $assets;
  }

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
  public function filterSkuAssetType(array $assets, $asset_type) {
    $filtered_assets = [];

    foreach ($assets as $asset) {
      if ((!empty($asset)) && ($asset['sortAssetType'] === $asset_type)) {
        $filtered_assets[] = $asset;
      }
    }

    return $filtered_assets;
  }

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
  public function getChildSkuAssets(SKU $sku, $context, array $avoid_assets = []) {
    $child_skus = $this->skuManager->getValidChildSkusAsString($sku);

    $assets = [];
    foreach ($child_skus ?? [] as $child_sku) {
      $assets[$child_sku] = $this->getSkuAssets($child_sku, $context, $avoid_assets);
    }

    return $assets;
  }

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
  public function getAssetsForSku(SKU $sku, $page_type) {
    $assets = [];
    if ($sku->bundle() == 'simple') {
      $assets = $this->getSkuAssets($sku, $page_type);
    }
    elseif ($sku->bundle() == 'configurable') {
      $assets = $this->getChildSkuAssets($sku, $page_type);
    }

    return $assets;
  }

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
    $type = (strpos($asset['Data']['AssetType'], 'MovingMedia') !== FALSE)
      ? 'video'
      : 'image';

    return $type;
  }

}
