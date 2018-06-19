<?php

namespace Drupal\alshaya_hm_images;

use Detection\MobileDetect;
use Drupal\acq_sku\AcquiaCommerce\SKUPluginManager;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;

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
   * Term Storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * SkuAssetManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config Factory service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku manager service.
   * @param \Drupal\acq_sku\AcquiaCommerce\SKUPluginManager $skuPluginManager
   *   Sku Plugin Manager.
   * @param \Drupal\acq_sku\AcquiaCommerce\SKUPluginManager $skuPluginManager
   *   Sku Plugin Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module Handler service object.
   */
  public function __construct(ConfigFactory $configFactory,
                              CurrentRouteMatch $currentRouteMatch,
                              SkuManager $skuManager,
                              SKUPluginManager $skuPluginManager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ModuleHandlerInterface $moduleHandler) {
    $this->configFactory = $configFactory;
    $this->currentRouteMatch = $currentRouteMatch;
    $this->skuManager = $skuManager;
    $this->skuPluginManager = $skuPluginManager;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Utility function to construct CDN url for the asset.
   *
   * @param mixed $sku
   *   SKU text or full entity object.
   * @param string $page_type
   *   Page on which the asset needs to be rendered.
   * @param array $location_images
   *   Location on page e.g., main image, thumbnails etc.
   * @param string $style
   *   Style string.
   * @param bool $first_image_only
   *   Return only the first image.
   *
   * @return array
   *   Array of urls to sku assets.
   */
  public function getSkuAssets($sku, $page_type, array $location_images, $style = '', $first_image_only = TRUE) {
    $sku = $sku instanceof SKU ? $sku->getSku() : $sku;

    $base_url = $this->configFactory->get('alshaya_hm_images.settings')->get('base_url');
    $sku_property_values = $this->skuManager->getSkuPropertyValue($sku, ['attr_assets__value']);

    if (($sku_property_values) &&
      !empty($unserialized_assets = unserialize($sku_property_values->attr_assets__value))) {
      $assets = $this->sortSkuAssets($sku, $page_type, $unserialized_assets);
    }

    $asset_variant_urls = [];
    $asset_urls = [];

    if (empty($assets)) {
      return [];
    }

    foreach ($location_images as $location_image) {
      $asset_urls = [];
      foreach ($assets as $asset) {
        list($set, $image_location_identifier) = $this->getAssetAttributes($sku, $asset, $page_type, $location_image, $style);

        // Prepare query options for image url.
        if (isset($set['url'])) {
          $url_parts = parse_url(urldecode($set['url']));
          if (!empty($url_parts['query'])) {
            parse_str($url_parts['query'], $query_options);
            // Overwrite the product style coming from season 5 image url with
            // the one based on context in which the image is being rendered.
            $query_options['call'] = 'url[' . $image_location_identifier . ']';
            $options = [
              'query' => $query_options,
            ];
          }
        }
        else {
          $options = [
            'query' => [
              'set' => implode(',', $set),
              'call' => 'url[' . $image_location_identifier . ']',
            ],
          ];
        }

        $asset_urls[] = [
          'url' => Url::fromUri($base_url, $options),
          'sortAssetType' => $asset['sortAssetType'],
          'sortFacingType' => $asset['sortFacingType'],
        ];

        if ($first_image_only) {
          return $asset_urls;
        }

        // Return specific image in case a match has been found for the swatch
        // type.
        if (($style) && ($asset['sortAssetType'] === $style)) {
          $swatch_asset_url[] = $asset_urls[count($asset_urls) - 1];
          return $swatch_asset_url;
        }

      }
      if (!empty($asset_urls)) {
        $asset_variant_urls[$location_image] = $asset_urls;
      }
    }

    // If there is only a single location_image, we don't want the results to be grouped.
    if (count($location_images) === 1) {
      return $asset_urls;
    }

    return $asset_variant_urls;
  }

  /**
   * Helper function to fetch asset attributes.
   *
   * @param string $sku
   *   SKU code for Product we fetching the assets for.
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
  public function getAssetAttributes($sku, array $asset, $page_type, $location_image, $style = '') {
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

      // Use res attribute in place of width & height while calculating images
      // for swatches.
      if (empty($style)) {
        $set['width'] = "width[" . $alshaya_hm_images_settings->get('dimensions')[$location_image]['width'] . "]";
        $set['height'] = "height[" . $alshaya_hm_images_settings->get('dimensions')[$location_image]['height'] . "]";
      }
      else {
        $detect = new MobileDetect();
        $set['res'] = "res[z]";

        if ($detect->isMobile()) {
          $set['res'] = "res[y]";
        }
      }


      // Check for overrides for style identifiers & dimensions.
      $config_overrides = $this->overrideConfig($sku, $page_type);

      // If overrides are available, update style id, width & height in the url.
      if (!empty($config_overrides)) {
        if (isset($config_overrides['style_identifiers'][$location_image])) {
          $image_location_identifier = $config_overrides['style_identifiers'][$location_image];
        }

        if ((!empty($style)) && isset($config_overrides['dimensions'][$location_image]['width'])) {
          $set['width'] = "width[" . $config_overrides['dimensions'][$location_image]['width'] . "]";
        }

        if ((!empty($style)) && isset($config_overrides['dimensions'][$location_image]['height'])) {
          $set['height'] = "height[" . $config_overrides['dimensions'][$location_image]['height'] . "]";
        }
      }
    }

    return [$set, $image_location_identifier];
  }

  /**
   * Helper function to check & override assets config.
   *
   * @param string $sku
   *   Sku code for Product whose assets are being filtered.
   * @param string $page_type
   *   Attributes for which we checking the override.
   *
   * @return array
   *   Overridden config in context of the category product belongs to.
   */
  public function overrideConfig($sku, $page_type) {
    $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

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
        $sku = !($sku instanceof SKU) ? SKU::loadFromSku($sku) : $sku;
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
  public function sortSkuAssets($sku, $page_type, array $assets) {
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
          $sort_angle_weights = array_flip($sort_angle_weights);
          uasort($asset, function ($a, $b) use ($sort_angle_weights) {
            // If weight is set in config, use that else use a default high
            // weight to push the items to bottom of the list.
            $weight_a = isset($sort_angle_weights[$a['sortFacingType']]) ? $sort_angle_weights[$a['sortFacingType']] : self::LP_DEFAULT_WEIGHT;
            $weight_b = isset($sort_angle_weights[$b['sortFacingType']]) ? $sort_angle_weights[$b['sortFacingType']] : self::LP_DEFAULT_WEIGHT;

            // Use number for sorting in case we land onto 2 images with same
            // asset type & facing type.
            if (($weight_a - $weight_b) === 0) {
              $weight_a = $a['Data']['Angle']['Number'];
              $weight_b = $b['Data']['Angle']['Number'];
            }

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
   * @param array $locations
   *   Location on page e.g., main image, thumbnails etc.
   * @param bool $first_only
   *   Flag to indicate we need the assets of the first child only.
   * @param bool $first_image_only
   *   Return only the first image.
   *
   * @return array
   *   Array of sku child assets.
   */
  public function getChildSkuAssets(SKU $sku, $context, array $locations, $first_only = TRUE, $first_image_only = TRUE) {
    $child_skus = $this->skuManager->getChildrenSkuIds($sku, $first_only);
    $assets = [];

    if (($first_only) && (!empty($child_skus))) {
      return $this->getSkuAssets($child_skus, $context, $locations, '', $first_image_only);
    }

    if (!empty($child_skus)) {
      foreach ($child_skus as $child_sku) {
        $assets[$sku->getSku()] = $this->getSkuAssets($child_sku, $context, $locations, '', $first_image_only);
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
      $term = $this->termStorage->load($tid);

      // If term object.
      if ($term instanceof TermInterface) {
        $swatch_type = ($term->get('field_swatch_type')->first()) ? $term->get('field_swatch_type')->getString() : self::LP_SWATCH_DEFAULT;
      }
    }

    return $swatch_type;
  }

  /**
   * Helper function to fetch list of color options supported by a parent SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Parent sku.
   *
   * @return array
   *   Array of RGB color values keyed by article_castor_id.
   */
  public function getColorsForSku(SKU $sku) {
    $child_skus = $this->skuManager->getChildrenSkuIds($sku);
    $article_castor_ids = [];
    $traversed_article_castor_ids = [];

    if (empty($child_skus)) {
      return [];
    }

    $child_sku_ids = $this->skuManager->getEntityIdsBySku($child_skus);
    $plugin_definition = $this->skuPluginManager->pluginFromSKU($sku);

    $class = $plugin_definition['class'];
    $plugin = new $class();

    foreach ($child_sku_ids as $child_sku) {
      // Avoid duplicate colors in cases of corrupt data.
      // e.g., color label= '' for rgb_color=#234567 &
      // color_label=grey for rgb_color=#234567. Also, return back
      // color label-code mapping uniquely identified by an article_castor_id.
      if (!empty($article_castor_id = $plugin->getAttributeValue($child_sku, 'article_castor_id')) &&
        (!in_array($article_castor_id, $traversed_article_castor_ids))) {
        $traversed_article_castor_ids[] = $article_castor_id;
        $color_attributes = $this->getColorAttributesFromSku($child_sku);
        $article_castor_ids[$color_attributes['attr_color_label']] = $color_attributes['attr_rgb_color'];
      }
    }

    return $article_castor_ids;
  }

  /**
   * Helper function to get SKU based on Castor Id.
   *
   * @param \Drupal\acq_sku\Entity\SKU $parent_sku
   *   Parent Sku.
   * @param int $rgb_color_label
   *   Castor id for which child sku needs to be fetched.
   *
   * @return array|SKU
   *   Array of SKUs or single SKU object matching the castor id.
   */
  public function getChildSkuFromColor(SKU $parent_sku, $rgb_color_label) {
    $child_skus = $this->skuManager->getChildrenSkuIds($parent_sku);

    if (empty($child_skus)) {
      return NULL;
    }

    foreach ($child_skus as $child_sku) {
      if (!empty($sku_attributes = $this->skuManager->getSkuPropertyValue($child_sku, ['attr_color_label'])) &&
        ($sku_attributes->attr_color_label == $rgb_color_label)) {
        return $child_sku;
      }
    }
  }

  /**
   * Helper function to get images for a SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Parent Sku.
   * @param string $page_type
   *   Type of page.
   * @param array $image_types
   *   Type of image.
   * @param bool $first_image_only
   *   Return only the first image.
   *
   * @return array|images
   *   Array of images for the SKU.
   */
  public function getImagesForSku(SKU $sku, $page_type, array $image_types, $first_image_only = TRUE) {
    $images = [];
    if ($sku->bundle() == 'simple') {
      $images = $this->getSkuAssets($sku, $page_type, $image_types, '', $first_image_only);
    }
    elseif ($sku->bundle() == 'configurable') {
      $images = $this->getChildSkuAssets($sku, $page_type, $image_types, TRUE, $first_image_only);
    }
    return $images;
  }

  /**
   * Helper function to get color label & rgb code for SKU.
   *
   * @param int $sku_id
   *   Entity id for the SKU being processed.
   *
   * @return array
   *   Associative array returning color label & code.
   */
  public function getColorAttributesFromSku($sku_id) {
    $current_langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $query = \Drupal::database()->select('acq_sku_field_data', 'asfd');
    $query->fields('asfd', ['attr_color_label', 'attr_rgb_color']);
    $query->condition('id', $sku_id);
    $query->condition('langcode', $current_langcode);
    return $query->execute()->fetchAssoc();
  }

}
