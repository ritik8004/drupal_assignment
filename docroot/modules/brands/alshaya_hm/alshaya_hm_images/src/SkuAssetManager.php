<?php

namespace Drupal\alshaya_hm_images;

use Drupal\acq_sku\AcquiaCommerce\SKUPluginManager;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;

/**
 * SkuAssetManager Class.
 */
class SkuAssetManager {

  /**
   * Constant to denote that the current asset has no angle data associated.
   */
  const LP_DEFAULT_ANGLE = 'NO_ANGLE';

  /**
   * Constant for default weight in case no weight has been set via config.
   */
  const LP_DEFAULT_WEIGHT = 100;

  /**
   * Constant for default swatch display type.
   */
  const LP_SWATCH_DEFAULT = 'RGB';

  /**
   * Constant for RGB swatch display type.
   */
  const LP_SWATCH_RGB = 'RGB';

  /**
   * The Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The Sku Plugin Manager service.
   *
   * @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginManager
   */
  protected $skuPluginManager;

  /**
   * SkuAssetManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config Factory service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku manager service.
   * @param SKUPluginManager $skuPluginManager
   *   Sku Plugin Manager.
   */
  public function __construct(ConfigFactory $configFactory,
                              CurrentRouteMatch $currentRouteMatch,
                              SkuManager $skuManager,
                              SKUPluginManager $skuPluginManager) {
    $this->configFactory = $configFactory;
    $this->currentRouteMatch = $currentRouteMatch;
    $this->skuManager = $skuManager;
    $this->skuPluginManager = $skuPluginManager;
  }

  /**
   * Utility function to construct CDN url for the asset.
   *
   * @param mixed $sku
   *   SKU text or full entity object.
   * @param string $page_type
   *   Page on which the asset needs to be rendered.
   * @param string $location_image
   *   Location on page e.g., main image, thumbnails etc.
   *
   * @return array
   *   Array of urls to sku assets.
   */
  public function getSkuAsset($sku, $page_type, $location_image, $style = "") {
    $sku = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

    if (!($sku instanceof SKU)) {
      return [];
    }

    $base_url = $this->configFactory->get('alshaya_hm_images.settings')->get('base_url');
    $assets = $this->sortSkuAssets($sku, $page_type, unserialize($sku->get('attr_assets')->value));
    $asset_urls = [];

    foreach ($assets as $asset) {
      list($set, $image_location_identifier)  = $this->getAssetAttributes($sku, $asset, $page_type, $location_image);

      // Prepare query options for image url.
      $options = [
        'query' => [
          'set' => implode(',', $set),
          'call' => 'url[' . $image_location_identifier . ']',
        ],
      ];

      $asset_urls[] = [
        'url' => (isset($set['url'])) ? Url::fromUri($set['url']) : Url::fromUri($base_url, $options),
        'sortAssetType' => $asset['sortAssetType'],
        'sortFacingType' => $asset['sortFacingType'],
      ];
    }
    if ($style) {
      foreach ($asset_urls as $asset) {
        if ($asset['sortAssetType'] === $style) {
          return [$asset];
        }
      }
    }

    return $asset_urls;
  }

  /**
   * Helper function to fetch asset attributes.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity for which we fetching the assets.
   * @param array $asset
   *   Asset array with all metadata.
   * @param string $page_type
   *   Page type on which this asset needs to be rendered.
   * @param string $location_image
   *   Location on page e.g., main image, thumbnails etc.
   *
   * @return array
   *   Array of asset attributes.
   */
  public function getAssetAttributes(SKU $sku, $asset, $page_type, $location_image) {
    $alshaya_hm_images_settings = $this->configFactory->get('alshaya_hm_images.settings');
    $image_location_identifier = $alshaya_hm_images_settings->get('style_identifiers')[$location_image];

    if (isset($asset['is_old_format']) && $asset['is_old_format']) {
      return [['url' => $asset['Url']], $image_location_identifier];
    }
    else {
      $origin = $alshaya_hm_images_settings->get('origin');

      $set['source'] = "source[/" . $asset['Data']['FilePath'] . "]";
      $set['origin'] = "origin[" . $origin . "]";
      $set['type'] = "type[" . $asset['sortAssetType'] . "]";
      $set['hmver'] = "hmver[" . $asset['Data']['Version'] . "]";
      $set['width'] = "width[" . $alshaya_hm_images_settings->get('dimensions')[$location_image]['width'] . "]";
      $set['height'] = "height[" . $alshaya_hm_images_settings->get('dimensions')[$location_image]['height'] . "]";

      // Check for overrides for style identifiers & dimensions.
      $config_overrides = $this->overrideConfig($sku, $page_type);

      // If overrides are available, update style id, width & height in the url.
      if (!empty($config_overrides)) {
        if (isset($config_overrides['style_identifiers'][$location_image])) {
          $image_location_identifier = $config_overrides['style_identifiers'][$location_image];
        }

        if (isset($config_overrides['dimensions'][$location_image]['width'])) {
          $set['width'] = "width[" . $config_overrides['dimensions'][$location_image]['width'] . "]";
        }

        if (isset($config_overrides['dimensions'][$location_image]['height'])) {
          $set['height'] = "height[" . $config_overrides['dimensions'][$location_image]['height'] . "]";
        }
      }
    }

    return [$set, $image_location_identifier];
  }

  /**
   * Helper function to check & override assets config.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Sku for which the assets are being filtered.
   * @param string $page_type
   *   Attributes for which we checking the override.
   *
   * @return array
   *   Overridden config in context of the category product belongs to.
   */
  public function overrideConfig(SKU $sku, $page_type) {
    $currentRoute['route_name'] = $this->currentRouteMatch->getRouteName();
    $currentRoute['route_params'] = $this->currentRouteMatch->getParameters()->all();
    $alshaya_hm_images_settings = $this->configFactory->get('alshaya_hm_images.settings');
    $overrides = $alshaya_hm_images_settings->get('overrides');
    $tid = NULL;

    // Identify the category for the product being displayed.
    switch ($page_type) {
      case 'plp':
        if (($currentRoute['route_name'] === 'entity.taxonomy_term.canonical') && (!empty($currentRoute['route_params']['taxonomy_term']))) {
          $taxonomy_term = $currentRoute['route_params']['taxonomy_term'];
          $tid = $taxonomy_term->id();
        }
        break;

      case 'pdp':
      case 'teaser':
      case 'swatch':
        $product_node = alshaya_acm_product_get_display_node($sku);
        if (($product_node) && ($terms = $product_node->get('field_category')->getValue())) {
          // Use the first term found with an override for
          // location identifier.
          $tid = $terms[0]['target_id'];

        }
        break;
    }

    return !empty($tid) && isset($overrides[$tid]) ? $overrides[$tid] : [];
  }

  /**
   * Helper function to sort based on angles.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU for which the assets needs to be sorted on angles.
   * @param string $page_type
   *   Page on which the asset needs to be rendered.
   * @param array $assets
   *   Array of mixed asset types.
   *
   * @return array
   *   Array of assets sorted by their asset types & angles.
   */
  public function sortSkuAssets(SKU $sku, $page_type, array $assets) {
    $alshaya_hm_images_config = $this->configFactory->get('alshaya_hm_images.settings');
    // Fetch weights of asset types based on the pagetype.
    $sku_asset_type_weights = $alshaya_hm_images_config->get('weights')[$page_type];

    // Fetch angle config.
    $sort_angle_weights = $alshaya_hm_images_config->get('weights')['angle'];

    // Check if there are any overrides for category this product page is
    // tagged with.
    $config_overrides = $this->overrideConfig($sku, $page_type);

    if (!empty($config_overrides)) {
      if (isset($config_overrides['weights']['angle'])) {
        $sort_angle_weights = $config_overrides['weights']['angle'];
      }

      if (isset($config_overrides['weights'][$page_type])) {
        $sku_asset_type_weights = $config_overrides['weights'][$page_type];
      }
    }
    // Create multi-dimensional array of assets keyed by their asset type.
    if (!empty($assets)) {
      $grouped_assets = [];
      foreach ($sku_asset_type_weights as $asset_type => $weight) {
        $grouped_assets[$asset_type] = $this->filterSkuAssetType($assets, $asset_type);
      }

      // Sort items based on the angle config.
      foreach ($grouped_assets as $key => $asset) {
        if (!empty($asset)) {
          uasort($asset, function ($a, $b) use ($sort_angle_weights) {
            // If weight is set in config, use that else use a default high
            // weight to push the items to bottom of the list.
            $weight_a = isset($sort_angle_weights[$a['sortFacingType']]) ? $sort_angle_weights[$a['sortFacingType']] : self::LP_DEFAULT_WEIGHT;
            $weight_b = isset($sort_angle_weights[$b['sortFacingType']]) ? $sort_angle_weights[$b['sortFacingType']] : self::LP_DEFAULT_WEIGHT;

            return $weight_a - $weight_b < 0 ? -1 : 1;
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
  public function filterSkuAssetType($assets, $asset_type) {
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
   * @param string $location
   *   Location on page e.g., main image, thumbnails etc.
   * @param bool $first_only
   *   Flag to indicate we need the assets of the first child only.
   *
   * @return array
   *   Array of sku child assets.
   */
  public function getChildSkuAssets(SKU $sku, $context, $location, $first_only = TRUE) {
    $child_skus = $this->skuManager->getChildSkus($sku);
    $assets = [];

    if ($child_skus) {
      foreach ($child_skus as $child_sku) {
        if ($child_sku instanceof SKU) {
          if ($first_only) {
            $assets = $this->getSkuAsset($child_sku, $context, $location);
            return $assets;
          }
          else {
            $assets[$sku->getSku()] = $this->getSkuAsset($child_sku, $context, $location);
          }
        }
      }
    }

    return $assets;
  }

  /**
   * Helper function to fetch swatch type for the sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Sku for which swatch type needs to be fetched.
   *
   * @return string
   *   Swatch type for the sku.
   */
  public function getSkuSwatchType(SKU $sku) {
    $swatch_type = self::LP_SWATCH_DEFAULT;
    $product_node = alshaya_acm_product_get_display_node($sku);

    if (($product_node) && ($terms = $product_node->get('field_category')->getValue())) {
      // Use the first term found with an override for
      // location identifier.
      $tid = $terms[0]['target_id'];
      $term = Term::load($tid);
      $swatch_type = ($term->get('field_swatch_type')->first()) ? $term->get('field_swatch_type')->getString() : self::LP_SWATCH_DEFAULT;

    }

    return $swatch_type;
  }

  /**
   * Helper function to fetch list of color options supported by a parent SKU.
   *
   * @param SKU $sku
   *   Parent sku.
   *
   * @return array
   *   Array of RGB color values keyed by article_castor_id.
   */
  public function getColorsForSku(SKU $sku) {
    $child_skus = $this->skuManager->getChildSkus($sku);
    $article_castor_ids = [];

    if (empty($child_skus)) {
      return [];
    }

    $plugin_definition = $this->skuPluginManager->pluginFromSKU($sku);

    $class = $plugin_definition['class'];
    $plugin = new $class();

    foreach ($child_skus as $key => $child_sku) {
      if ($child_sku instanceof SKU) {
        if (!isset($article_castor_ids[$plugin->getAttributeValue($child_sku, 'article_castor_id')])) {
          $article_castor_ids[$child_sku->get('attr_color_label')->value] = $child_sku->get('attr_rgb_color')->value;
        }
      }
    }

    return $article_castor_ids;
  }

  /**
   * Helper function to get SKU based on Castor Id.
   *
   * @param SKU $parent_sku
   *   Parent Sku.
   * @param int $rgb_color_label
   *   Castor id for which child sku needs to be fetched.
   *
   * @return array|SKU
   *   Array of SKUs or single SKU object matching the castor id.
   */
  public function getChildSkuFromColor(SKU $parent_sku, $rgb_color_label) {
    $child_skus = $this->skuManager->getChildSkus($parent_sku);

    if (empty($child_skus)) {
      return NULL;
    }

    foreach ($child_skus as $child_sku) {
      if (($child_sku instanceof SKU) && $child_sku->get('attr_color_label')->value == $rgb_color_label) {
        return $child_sku;
      }
    }
  }

}
