<?php

namespace Drupal\alshaya_hm_images;

use Detection\MobileDetect;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\AcquiaCommerce\SKUPluginManager;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Service\ProductCacheManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Drupal\file\FileInterface;
use Drupal\taxonomy\TermInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

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
   * File Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Product Cache Manager.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductCacheManager
   */
  protected $productCacheManager;

  /**
   * PIMS ID <=> FID cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cachePimsFiles;

  /**
   * GuzzleHttp\Client definition.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module Handler service object.
   * @param \Drupal\alshaya_acm_product\Service\ProductCacheManager $product_cache_manager
   *   Product Cache Manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_pims_files
   *   PIMS ID <=> Drupal File URI cache.
   */
  public function __construct(ConfigFactory $configFactory,
                              CurrentRouteMatch $currentRouteMatch,
                              SkuManager $skuManager,
                              SKUPluginManager $skuPluginManager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ModuleHandlerInterface $moduleHandler,
                              ProductCacheManager $product_cache_manager,
                              CacheBackendInterface $cache_pims_files,
                              Client $http_client) {
    $this->configFactory = $configFactory;
    $this->currentRouteMatch = $currentRouteMatch;
    $this->skuManager = $skuManager;
    $this->skuPluginManager = $skuPluginManager;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->moduleHandler = $moduleHandler;
    $this->productCacheManager = $product_cache_manager;
    $this->cachePimsFiles = $cache_pims_files;
    $this->httpClient = $http_client;
  }

  public function getAssets(SKU $sku) {
    $static = &drupal_static(__METHOD__, []);
    $cid = implode(':', [
      $sku->id(),
      $sku->language()->getId(),
    ]);

    if (isset($static[$cid])) {
      return $static[$cid];
    }

    $assets = unserialize($sku->get('attr_assets')->getString());

    if (!is_array($assets) || empty($assets)) {
      return [];
    }

    foreach ($assets as &$data) {
      if (isset($data['pims_image'], $data['pims_image']['id'])) {
        $data['drupal_uri'] = $this->getImageUri($data['pims_image']);
      }
    }

    $static[$cid] = $assets;
    return $assets;
  }

  private function getFileUriFromPimsId($pims_id) {
    $cache = $this->cachePimsFiles->get($pims_id);
    return $cache->data ?? NULL;
  }

  private function setFileUriForPimsId($pims_id, FileInterface $file) {
    $this->cachePimsFiles->set($pims_id, $file->getFileUri(), Cache::PERMANENT, $file->getCacheTags());
  }

  private function downloadPimsImage($data) {
    $base_url = $this->getBaseUrl();

    $url = $base_url . $data['path'] . $data['filename'];

    // Download the file contents.
    try {
      $file_data = $this->httpClient->get($url)->getBody();

      if (empty($file_data)) {
        throw new \Exception('Failed to download asset file: ' . $url);
      }
    }
    catch (RequestException $e) {
      watchdog_exception('SkuAssetManager', $e);

      // Not able to download image, no further processing required.
      return NULL;
    }

    // Prepare the directory path.
    $directory = 'public://assets' . $data['path'];

    // Prepare the directory.
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

    try {
      $file = file_save_data($file_data, $directory . '/' . $data['filename'], FILE_EXISTS_REPLACE);

      if (!($file instanceof FileInterface)) {
        throw new \Exception('Failed to save asset file: ' . $url);
      }
    }
    catch (\Exception $e) {
      watchdog_exception('SkuAssetManager', $e);
    }

    $this->setFileUriForPimsId($data['id'], $file);

    return $file->getFileUri();
  }

  private function getImageUri($data) {
    // First check if we have fid for pims id.
    $uri = $this->getFileUriFromPimsId($data['id']);

    if (empty($uri)) {
      $uri = $this->downloadPimsImage($data);
    }

    return $uri;
  }

  private function getBaseUrl() {
    static $base_url;

    if (empty($base_url)) {
      $base_url = $this->configFactory
        ->get('alshaya_hm_images.settings')
        ->get('base_url');
    }

    return $base_url;
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
   * @param array $avoid_assets
   *   (optional) Array of AssetId to avoid.
   *
   * @return array
   *   Array of urls to sku assets.
   */
  public function getSkuAssets($sku, $page_type, array $location_images, $style = '', $first_image_only = TRUE, array $avoid_assets = []) {
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

    $asset_variant_urls = [];
    $asset_urls = [];
    $base_url = $this->getBaseUrl();

    foreach ($location_images as $location_image) {
      $asset_urls = [];
      foreach ($assets as $asset) {
        if (!empty($avoid_assets) && isset($asset['Data']['AssetId']) && in_array($asset['Data']['AssetId'], $avoid_assets)) {
          continue;
        }

        list($set, $image_location_identifier) = $this->getAssetAttributes($sku, $asset, $page_type, $location_image, $style);

        $query_options = $this->getAssetQueryString($set, $image_location_identifier);

        $asset_url = [
          'url' => Url::fromUri($base_url, ['query' => $query_options]),
          'sortAssetType' => $asset['sortAssetType'],
          'sortFacingType' => $asset['sortFacingType'],
          'Data' => $asset['Data'] ?? [],
        ];

        // Prepare raw url without res and call.
        unset($set['res']);
        $raw_query_options = $this->getAssetQueryString($set, $image_location_identifier);
        unset($raw_query_options['call']);
        $asset_url['raw_url'] = Url::fromUri($base_url, ['query' => $raw_query_options]);

        $asset_urls[] = $asset_url;

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

    // If there is only a single location_image, we don't want the results to
    // be grouped.
    if (count($location_images) === 1) {
      return $asset_urls;
    }

    return $asset_variant_urls;
  }

  /**
   * Prepare query string for assets.
   *
   * @param array $set
   *   Set data.
   * @param string $image_location_identifier
   *   Image location identifier.
   *
   * @return array
   *   Query string.
   */
  private function getAssetQueryString(array $set, string $image_location_identifier): array {
    // Prepare query options for image url.
    if (isset($set['url'])) {
      $url_parts = parse_url(urldecode($set['url']));
      if (!empty($url_parts['query'])) {
        parse_str($url_parts['query'], $query_options);
        // Overwrite the product style coming from season 5 image url with
        // the one based on context in which the image is being rendered.
        $query_options['call'] = 'url[' . $image_location_identifier . ']';
      }
    }
    else {
      $query_options = [
        'set' => implode(',', $set),
        'call' => 'url[' . $image_location_identifier . ']',
      ];
    }

    return $query_options;
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
   * @param string $style
   *   CSS style.
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

      $set['res'] = "res[" . $alshaya_hm_images_settings->get('dimensions')[$location_image]['desktop'] . "]";
      $detect = new MobileDetect();
      if ($detect->isMobile()) {
        $set['res'] = "res[" . $alshaya_hm_images_settings->get('dimensions')[$location_image]['mobile'] . "]";
      }

      // Check for overrides for style identifiers & dimensions.
      $config_overrides = $this->overrideConfig($sku, $page_type);

      // If overrides are available, update style id, width & height in the url.
      if (!empty($config_overrides)) {
        if (isset($config_overrides['style_identifiers'][$location_image])) {
          $image_location_identifier = $config_overrides['style_identifiers'][$location_image];
        }

        if ((!empty($style)) && isset($config_overrides['dimensions'][$location_image]['desktop'])) {
          $set['res'] = "res[" . $config_overrides['dimensions'][$location_image]['desktop'] . "]";
        }

        $detect = new MobileDetect();
        if ($detect->isMobile()) {
          if ((!empty($style)) && isset($config_overrides['dimensions'][$location_image]['mobile'])) {
            $set['res'] = "res[" . $config_overrides['dimensions'][$location_image]['mobile'] . "]";
          }
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
    // @TODO: Check and remove this include.
    $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

    $alshaya_hm_images_settings = $this->configFactory->get('alshaya_hm_images.settings');
    $overrides = $alshaya_hm_images_settings->get('overrides');

    // No further processing if overrides is empty.
    if (empty($overrides)) {
      return [];
    }

    $currentRoute['route_name'] = $this->currentRouteMatch->getRouteName();
    $currentRoute['route_params'] = $this->currentRouteMatch->getParameters()->all();

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
        $product_node = $this->skuManager->getDisplayNode($sku);
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
   * @param array $locations
   *   Location on page e.g., main image, thumbnails etc.
   * @param bool $first_only
   *   Flag to indicate we need the assets of the first child only.
   * @param bool $first_image_only
   *   Return only the first image.
   * @param array $avoid_assets
   *   (optional) Array of AssetId to avoid.
   *
   * @return array
   *   Array of sku child assets.
   */
  public function getChildSkuAssets(SKU $sku, $context, array $locations, $first_only = TRUE, $first_image_only = TRUE, array $avoid_assets = []) {
    $child_skus = $this->skuManager->getValidChildSkusAsString($sku);
    $assets = [];

    if (($first_only) && (!empty($child_skus))) {
      $child_sku = reset($child_skus);
      return $this->getSkuAssets($child_sku, $context, $locations, '', $first_image_only, $avoid_assets);
    }

    if (!empty($child_skus)) {
      foreach ($child_skus as $child_sku) {
        $assets[$sku->getSku()] = $this->getSkuAssets($child_sku, $context, $locations, '', $first_image_only, $avoid_assets);
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
    $product_node = $this->skuManager->getDisplayNode($sku);

    if (($product_node) && ($terms = $product_node->get('field_category')->getValue())) {
      if (!empty($terms)) {
        foreach ($terms as $value) {
          $tid = $value['target_id'];
          $term = $this->termStorage->load($tid);
          if ($term instanceof TermInterface) {
            $swatch_type = ($term->get('field_swatch_type')->first()) ? $term->get('field_swatch_type')->getString() : self::LP_SWATCH_DEFAULT;
            break;
          }
        }
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
    if ($sku->bundle() != 'configurable') {
      return [];
    }

    if ($cache = $this->productCacheManager->get($sku, 'hm_colors_for_sku')) {
      return $cache;
    }

    $combinations = $this->skuManager->getConfigurableCombinations($sku);
    if (empty($combinations)) {
      return [];
    }

    $article_castor_ids = [];
    foreach ($combinations['attribute_sku']['article_castor_id'] ?? [] as $skus) {
      $child_sku_entity = NULL;
      $color_attributes = [];

      // Use only the first SKU for which we get color attributes.
      foreach ($skus as $child_sku) {
        // Show only for colors for which we have stock.
        $child_sku_entity = SKU::loadFromSku($child_sku);

        if ($child_sku_entity instanceof SKUInterface && $this->skuManager->isProductInStock($child_sku_entity)) {
          $color_attributes = $this->getColorAttributesFromSku($child_sku_entity->id());
          if ($color_attributes) {
            break;
          }
        }
      }

      if ($child_sku_entity instanceof SKUInterface && $color_attributes) {
        $article_castor_ids[$child_sku_entity->id()] = $color_attributes['attr_rgb_color'];
      }
    }

    $this->productCacheManager->set($sku, 'hm_colors_for_sku', $article_castor_ids);

    return $article_castor_ids;
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
