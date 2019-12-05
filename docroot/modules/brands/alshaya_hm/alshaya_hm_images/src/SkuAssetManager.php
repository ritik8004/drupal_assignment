<?php

namespace Drupal\alshaya_hm_images;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\AcquiaCommerce\SKUPluginManager;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\Service\ProductCacheManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\taxonomy\TermInterface;
use GuzzleHttp\Client;

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
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Config alshaya_hm_images.settings.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $hmImageSettings;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * Date Time Service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  /**
   * File Usage.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  private $fileUsage;

  /**
   * Lock service.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  private $lock;

  /**
   * Cache for media_file_mapping.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cacheMediaFileMapping;

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
   * @param \GuzzleHttp\Client $http_client
   *   HTTP Client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Channel Factory.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Date Time service.
   * @param \Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   File Usage.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   Lock service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_media_file_mapping
   *   Cache for media_file_mapping.
   */
  public function __construct(ConfigFactory $configFactory,
                              CurrentRouteMatch $currentRouteMatch,
                              SkuManager $skuManager,
                              SKUPluginManager $skuPluginManager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ModuleHandlerInterface $moduleHandler,
                              ProductCacheManager $product_cache_manager,
                              CacheBackendInterface $cache_pims_files,
                              Client $http_client,
                              LoggerChannelFactoryInterface $logger_factory,
                              TimeInterface $time,
                              FileUsageInterface $file_usage,
                              LockBackendInterface $lock,
                              CacheBackendInterface $cache_media_file_mapping) {
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
    $this->logger = $logger_factory->get('SkuAssetManager');
    $this->time = $time;
    $this->fileUsage = $file_usage;
    $this->lock = $lock;
    $this->cacheMediaFileMapping = $cache_media_file_mapping;

    $this->hmImageSettings = $this->configFactory->get('alshaya_hm_images.settings');
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

    $save = FALSE;

    foreach ($assets as $index => &$asset) {
      // Sanity check, we always need asset id.
      if (empty($asset['Data']['AssetId'])) {
        unset($assets[$index]);
        continue;
      }

      // Check if we already have fid and drupal_uri in asset data.
      if (!empty($asset['fid']) && !empty($asset['drupal_uri'])) {
        $file = $this->fileStorage->load($asset['fid']);

        // Verify file entity exists. We might remove this check in future.
        if ($file instanceof FileInterface) {
          continue;
        }

        $this->logger->warning('Fid @fid available in asset for sku @sku but not available in system. Trying to download again.', [
          '@fid' => $asset['fid'],
          '@sku' => $sku->getSku(),
        ]);

        unset($asset['fid']);
      }

      if (empty(SKU::$downloadImage)) {
        unset($assets[$index]);
        continue;
      }

      // Use pims/asset id for lock key.
      try {
        $file = $this->downloadImage($asset, $sku->getSku());
      }
      catch (\Exception $e) {
        watchdog_exception('SkuAssetManager', $e);
      }

      // Skipped image download due to bad images.
      if (isset($asset['blacklist_expiry'])) {
        $save = TRUE;
      }

      if ($file instanceof FileInterface) {
        $this->fileUsage->add($file, $sku->getEntityTypeId(), $sku->getEntityTypeId(), $sku->id());

        $asset['drupal_uri'] = $file->getFileUri();
        $asset['fid'] = $file->id();
        $save = TRUE;
      }

      if (empty($asset['fid'])) {
        unset($assets[$index]);
      }
    }

    if ($save) {
      $sku->get('attr_assets')->setValue(serialize($assets));
      $sku->save();

      $this->logger->notice('Downloaded new asset images for sku @sku.', [
        '@sku' => $sku->getSku(),
      ]);
    }

    return array_filter($assets, function ($row) {
      return !empty($row['fid']);
    });
  }

  /**
   * Download image for PIMS Data and store in Drupal.
   *
   * @param array $data
   *   PIMS Data.
   * @param string $sku
   *   SKU of asset.
   *
   * @return \Drupal\file\FileInterface|null
   *   File entity if image download successful.
   */
  private function downloadPimsImage(array &$data, string $sku) {
    // If image is blacklisted, block download.
    if (isset($data['blacklist_expiry']) && time() < $data['blacklist_expiry']) {
      return FALSE;
    }

    $base_url = $this->hmImageSettings->get('pims_base_url');
    $pims_directory = $this->hmImageSettings->get('pims_directory');

    // Prepare the directory path.
    $directory = 'public://assets-shared/' . trim($data['path'], '/');
    $target = $directory . DIRECTORY_SEPARATOR . $data['filename'];

    // Check if file already exists in the directory.
    if (file_exists($target)) {
      // If file exists in directory, check if file entity exists.
      $files = reset($this->fileStorage->loadByProperties(['uri' => $target]));
      if (!empty($files) && $files instanceof FileInterface) {
        return $files;
      }
    }

    $url = implode('/', [
      trim($base_url, '/'),
      trim($pims_directory, '/'),
      trim($data['path'], '/'),
      trim($data['filename'], '/'),
    ]);

    // Download the file contents.
    try {
      $options = [
        'timeout' => Settings::get('media_download_timeout', 5),
      ];

      $file_stream = $this->httpClient->get($url, $options);
      $file_data = $file_stream->getBody();
      $file_data_length = $file_stream->getHeader('Content-Length');
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to download asset file for sku @sku from @url, error: @message', [
        '@sku' => $sku,
        '@url' => $url,
        '@message' => $e->getMessage(),
      ]);
    }

    // Check to ensure empty file is not saved in SKU.
    // Using Content-Length Header to check for valid image data, sometimes we
    // also get a 0 byte image with response 200 instead of 404.
    // So only checking $file_data is not enough.
    if (!isset($file_data_length) || $file_data_length[0] === '0') {
      // @TODO: SAVE blacklist info in a way so it does not have dependency on SKU.
      // Blacklist this image URL to prevent subsequent downloads for 1 day.
      $data['blacklist_expiry'] = strtotime('+1 day');
      // Leave a message for developers to find out why this happened.
      $this->logger->error('Empty file detected during download, blacklisted for 1 day from now. File remote id: @remote_id, File URL: @url on SKU @sku. @trace', [
        '@url' => $url,
        '@sku' => $sku,
        '@remote_id' => $data['filename'],
        '@trace' => json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)),
      ]);
      return FALSE;
    }

    // Check if image was blacklisted, remove it from blacklist.
    if (isset($data['blacklist_expiry'])) {
      unset($data['blacklist_expiry']);
    }

    // Prepare the directory.
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);
    try {
      $file = file_save_data($file_data, $target, FileSystemInterface::EXISTS_REPLACE);

      if (!($file instanceof FileInterface)) {
        throw new \Exception('Failed to save asset file');
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to save asset file for sku @sku at @uri, error: @message', [
        '@sku' => $sku,
        '@uri' => $target,
        '@message' => $e->getMessage(),
      ]);
    }

    return $file ?? [];
  }

  /**
   * Download image from Liquid Pixel to Drupal.
   *
   * @param array $asset
   *   Asset data.
   * @param string $sku
   *   SKU of asset.
   *
   * @return \Drupal\file\FileInterface|null
   *   File entity download successful.
   */
  private function downloadLiquidPixelImage(array &$asset, string $sku) {
    // If image is blacklisted, block download.
    if (isset($asset['blacklist_expiry']) && time() < $asset['blacklist_expiry']) {
      return FALSE;
    }

    $skipped_key = 'skipped_' . $asset['Data']['AssetId'];
    $cache = $this->cachePimsFiles->get($skipped_key);
    if (isset($cache, $cache->data)) {
      return NULL;
    }

    // Prepare the directory path.
    $directory = 'public://assets-lp-shared/' . trim(dirname($asset['Data']['FilePath']), '/');
    $target = $directory . DIRECTORY_SEPARATOR . basename($asset['Data']['FilePath']);

    // Check if file already exists in the directory.
    if (file_exists($target)) {
      // If file exists in directory, check if file entity exists.
      $files = reset($this->fileStorage->loadByProperties(['uri' => $target]));
      if (!empty($files) && $files instanceof FileInterface) {
        return $files;
      }
    }

    $url = $this->getSkuAssetUrlLiquidPixel($asset);

    // Download the file contents.
    try {
      $file_stream = $this->httpClient->get($url);
      $file_data = $file_stream->getBody();
      $file_data_length = $file_stream->getHeader('Content-Length');
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to download asset file for sku @sku from @url, error: @message', [
        '@sku' => $sku,
        '@url' => $url,
        '@message' => $e->getMessage(),
      ]);
    }

    // Check to ensure empty file is not saved in SKU.
    // Using Content-Length Header to check for valid image data, sometimes we
    // also get a 0 byte image with response 200 instead of 404.
    // So only checking $file_data is not enough.
    if (!isset($file_data_length) || $file_data_length[0] === '0') {
      // @TODO: SAVE blacklist info in a way so it does not have dependency on SKU.
      // Blacklist this image URL to prevent subsequent downloads for 1 day.
      $asset['blacklist_expiry'] = strtotime('+1 day');
      // Leave a message for developers to find out why this happened.
      $this->logger->error('Empty file detected during download, blacklisted for 1 day from now. File remote id: @remote_id, File URL: @url on SKU @sku. @trace', [
        '@url' => $url,
        '@sku' => $sku,
        '@remote_id' => $asset['Data']['AssetId'],
        '@trace' => json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)),
      ]);
      return FALSE;
    }

    // Check if image was blacklisted, remove it from blacklist.
    if (isset($asset['blacklist_expiry'])) {
      unset($asset['blacklist_expiry']);
    }

    $file_data = (string) $file_data;
    if (strlen($file_data) <= Settings::get('hm_grey_image_size', 24211)) {
      $this->logger->error('Skipping grey image. File: @file, Size: @size, Asset id: @id', [
        '@file' => $url,
        '@size' => strlen($file_data),
        '@id' => $asset['Data']['AssetId'],
      ]);

      // Cache it for a day so we can check for it again after one day.
      $this->cachePimsFiles->set($skipped_key, $this->time->getCurrentTime() + 86400);

      return NULL;
    }

    // Prepare the directory.
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

    try {
      $file = file_save_data($file_data, $target, FileSystemInterface::EXISTS_REPLACE);

      if (!($file instanceof FileInterface)) {
        throw new \Exception('Failed to save asset file: ' . $url);
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to save asset file for sku @sku at @uri, error: @message', [
        '@sku' => $sku,
        '@uri' => $target,
        '@message' => $e->getMessage(),
      ]);
    }

    return $file ?? NULL;
  }

  /**
   * Download Drupal file for Asset.
   *
   * @param array $asset
   *   Asset data.
   * @param string $sku
   *   SKU of asset.
   *
   * @return \Drupal\file\FileInterface|null
   *   File from asset if available.
   *
   * @throws \Exception
   */
  private function downloadImage(array &$asset, string $sku) {
    $lock_key = '';

    // Allow disabling this through settings.
    if (Settings::get('media_avoid_parallel_downloads', 1)) {

      $id = $asset['pims_image']['id'] ?? $asset['Data']['AssetId'];
      $lock_key = 'download_image_' . $id;

      // Acquire lock to ensure parallel processes are executed one by one.
      do {
        $lock_acquired = $this->lock->acquire($lock_key);

        // Sleep for half a second before trying again.
        if (!$lock_acquired) {
          usleep(500000);

          // Check once if downloaded by another process.
          $cache = $this->cacheMediaFileMapping->get($lock_key);
          if ($cache && $cache->data) {
            $file = $this->fileStorage->load($cache->data);
            if ($file instanceof FileInterface) {
              return $file;
            }

            throw new \Exception(sprintf('File id %s mapped for %s in cache invalid, not retrying', $cache->data, $id));
          }
        }
      } while (!$lock_acquired);
    }

    $file = NULL;
    if (isset($asset['pims_image']) && is_array($asset['pims_image'])) {
      $file = $this->downloadPimsImage($asset['pims_image'], $sku);
    }
    elseif ($this->hmImageSettings->get('fallback_to_liquidpixel')) {
      $file = $this->downloadLiquidPixelImage($asset, $sku);
    }

    if ($file instanceof FileInterface) {
      if ($lock_key) {
        // Add file id in cache for other processes to be able to use.
        $this->cacheMediaFileMapping->set($lock_key, $file->id(), $this->time->getRequestTime() + 120);
      }

      $this->logger->notice('Downloaded file @fid, uri @uri for Asset @id', [
        '@fid' => $file->id(),
        '@uri' => $file->getFileUri(),
        '@id' => $id,
      ]);
    }

    if ($lock_key) {
      $this->lock->release($lock_key);
    }

    return $file;
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
   * Utility function to construct CDN url for the asset.
   *
   * @param array $asset
   *   Asset data.
   *
   * @return string
   *   Asset url.
   */
  private function getSkuAssetUrlLiquidPixel(array $asset) {
    $base_url = $this->hmImageSettings->get('base_url');
    list($set, $image_location_identifier) = $this->getAssetAttributes($asset, 'pdp_fullscreen');
    $query_options = $this->getAssetQueryString($set, $image_location_identifier);
    return Url::fromUri($base_url, ['query' => $query_options])->toString();
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
   * @param array $asset
   *   Asset array with all metadata.
   * @param string $location_image
   *   Location on page e.g., main image, thumbnails etc.
   *
   * @return array
   *   Array of asset attributes.
   */
  public function getAssetAttributes(array $asset, $location_image) {
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
   *
   * @return array|images
   *   Array of images for the SKU.
   */
  public function getImagesForSku(SKU $sku, $page_type) {
    $images = [];
    if ($sku->bundle() == 'simple') {
      $images = $this->getSkuAssets($sku, $page_type);
    }
    elseif ($sku->bundle() == 'configurable') {
      $images = $this->getChildSkuAssets($sku, $page_type);
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
