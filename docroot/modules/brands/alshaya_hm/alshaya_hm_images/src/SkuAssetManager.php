<?php

namespace Drupal\alshaya_hm_images;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;

/**
 * SkuAssetManager Class.
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
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * SkuAssetManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config Factory service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku manager service.
   */
  public function __construct(ConfigFactory $configFactory,
                              CurrentRouteMatch $currentRouteMatch,
                              SkuManager $skuManager) {
    $this->configFactory = $configFactory;
    $this->currentRouteMatch = $currentRouteMatch;
    $this->skuManager = $skuManager;
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
  public function getSkuAsset($sku, $page_type, $location_image) {
    $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

    if (!($sku_entity instanceof SKU)) {
      return [];
    }

    $base_url = $this->configFactory->get('alshaya_hm_images.settings')->get('base_url');
    $assets = $this->sortSkuAsset($sku, $page_type, unserialize($sku_entity->get('attr_assets')->value));
    $asset_urls = [];

    foreach ($assets as $asset) {
      $set = $this->getAssetAttributes($sku, $asset, $page_type, $location_image);
      $image_location_identifier = $set['image_location_identifier'];
      unset($set['image_location_identifier']);

      // Prepare query options for image url.
      $options = [
        'query' => [
          'set' => implode(',', $set),
          'call' => 'url[' . $image_location_identifier . ']',
        ],
      ];

      $asset_urls[] = [
        'url' => Url::fromUri($base_url, $options),
        'sortAssetType' => $asset['sortAssetType'],
        'sortFacingType' => $asset['sortFacingType'],
      ];
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
    $origin = $alshaya_hm_images_settings->get('origin');

    $set['source'] = "source[/" . $asset['Data']['FilePath'] . "]";
    $set['origin'] = "origin[" . $origin . "]";
    $set['type'] = "type[" . $asset['sortAssetType'] . "]";
    $set['hmver'] = "hmver[" . $asset['Data']['Version'] . "]";
    $set['width'] = "width[" . $alshaya_hm_images_settings->get('dimensions')[$location_image]['width'] . "]";
    $set['height'] = "height[" . $alshaya_hm_images_settings->get('dimensions')[$location_image]['height'] . "]";
    $set['image_location_identifier'] = $alshaya_hm_images_settings->get('style_identifiers')[$location_image];

    // Check for overrides for style identifiers & dimensions.
    $config_overrides = $this->overrideConfig($sku, $page_type);

    // If overrides are available, update style id, width & height in the url.
    if (!empty($config_overrides)) {
      if (isset($config_overrides['style_identifiers'][$location_image])) {
        $set['image_location_identifier'] = $config_overrides['style_identifiers'][$location_image];
      }

      if (isset($config_overrides['dimensions'][$location_image]['width'])) {
        $set['width'] = "width[" . $config_overrides['dimensions'][$location_image]['width'] . "]";
      }

      if (isset($config_overrides['dimensions'][$location_image]['height'])) {
        $set['height'] = "height[" . $config_overrides['dimensions'][$location_image]['height'] . "]";
      }
    }

    return $set;
  }

  /**
   * Helper function to sort assets array in context of Page & category.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity for which assets need to be sorted.
   * @param string $page_type
   *   Page type for which assets need to be sorted.
   * @param array $assets
   *   Assets that need to be sorted.
   *
   * @return array
   *   Sorted assets array.
   */
  public function sortSkuAsset(SKU $sku, $page_type, array $assets) {
    $sort_assets_config = $this->configFactory->get('alshaya_hm_images.settings');
    $sort_assets_config_context = $sort_assets_config->get('weights')[$page_type];

    // Check if there are any overrides for category this product page is
    // tagged with.
    // Check for overrides for style identifiers & dimensions.
    $config_overrides = $this->overrideConfig($sku, $page_type);

    if (!empty($config_overrides)) {
      if (isset($config_overrides['weights'][$page_type])) {
        $sort_assets_config_context = $config_overrides['weights'][$page_type];
      }
    }

    uasort($assets, function ($a, $b) use ($sort_assets_config_context) {
      // If weight is set in config, use that else use a default high weight to
      // push the items to bottom of the list.
      $weight_a = isset($sort_assets_config_context[$a['sortAssetType']]) ? $sort_assets_config_context[$a['sortAssetType']] : 100;
      $weight_b = isset($sort_assets_config_context[$b['sortAssetType']]) ? $sort_assets_config_context[$b['sortAssetType']] : 100;

      return $weight_a - $weight_b < 0 ? -1 : 1;
    });

    return $assets;
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
   * Helper function to process assets for PLP/search pages.
   *
   * Extract main & hover image as per the business rules.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Sku entity for which PLP assets are being processed.
   * @param array $assets
   *   Assets to be processed for listing page.
   *
   * @return array
   *   Filtered assets array containing main & hover image.
   */
  public function processAssetsForListing(SKU $sku, array $assets) {
    if (empty($assets)) {
      return [];
    }
    $plp_image = [];

    // Fetch angle config.
    $sort_angles = $this->configFactory->get('alshaya_hm_images.settings')->get('weights')['angle'];

    // Check for overrides for style identifiers & dimensions.
    $config_overrides = $this->overrideConfig($sku, 'plp');

    if (!empty($config_overrides)) {
      if (isset($config_overrides['weights']['angle'])) {
        $sort_angles = $config_overrides['weights']['angle'];
      }
    }

    // Loop over assets array to find the main & hover image.
    foreach ($assets as $asset) {
      if (!empty($plp_image)) {
        break;
      }

      foreach ($sort_angles as $angle) {
        if ($asset['sortFacingType'] === $angle) {
          $plp_image = $asset;
          break;
        }
      }
    }

    return $plp_image;
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
        if ($first_only) {
          $assets = $this->getSkuAsset($child_sku, $context, $location);
          return $assets;
        }
        else {
          $assets[$sku->getSku()] = $this->getSkuAsset($child_sku, $context, $location);
        }
      }
    }

    return $assets;
  }

}
