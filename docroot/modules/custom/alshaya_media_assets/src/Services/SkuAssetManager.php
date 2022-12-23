<?php

namespace Drupal\alshaya_media_assets\Services;

use Drupal\acq_sku\AcqSkuConfig;
use Drupal\acq_sku\AcquiaCommerce\SKUPluginManager;
use Drupal\acq_sku\Entity\SKU;
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
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use GuzzleHttp\Client;
use Drupal\file\Entity\File;
use GuzzleHttp\TransferStats;

/**
 * Sku Asset Manager Class.
 */
class SkuAssetManager {

  /**
   * Constant to denote that the current asset has no angle data associated.
   */
  public const LP_DEFAULT_ANGLE = 'NO_ANGLE';

  /**
   * Constant for RGB swatch display type.
   */
  public const LP_SWATCH_RGB = 'RGB';

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
   * Config alshaya_acm_product.settings.
   *
   * @var Drupal\Core\Config\ImmutableConfig
   */
  protected $acmProductSettings;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

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
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File system service.
   */
  public function __construct(ConfigFactory $configFactory,
                              CurrentRouteMatch $currentRouteMatch,
                              SkuManager $skuManager,
                              SKUPluginManager $skuPluginManager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ModuleHandlerInterface $moduleHandler,
                              CacheBackendInterface $cache_pims_files,
                              Client $http_client,
                              LoggerChannelFactoryInterface $logger_factory,
                              TimeInterface $time,
                              FileUsageInterface $file_usage,
                              LockBackendInterface $lock,
                              CacheBackendInterface $cache_media_file_mapping,
                              FileSystemInterface $file_system) {
    $this->configFactory = $configFactory;
    $this->currentRouteMatch = $currentRouteMatch;
    $this->skuManager = $skuManager;
    $this->skuPluginManager = $skuPluginManager;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->moduleHandler = $moduleHandler;
    $this->cachePimsFiles = $cache_pims_files;
    $this->httpClient = $http_client;
    $this->logger = $logger_factory->get('SkuAssetManager');
    $this->time = $time;
    $this->fileUsage = $file_usage;
    $this->lock = $lock;
    $this->cacheMediaFileMapping = $cache_media_file_mapping;
    $this->fileSystem = $file_system;
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
    // phpcs:ignore
    $assets = unserialize($sku->get('attr_assets')->getString());

    if (!is_array($assets) || empty($assets)) {
      return [];
    }

    $save = FALSE;
    $download = TRUE;

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

      $this->acmProductSettings = $this->configFactory->get('alshaya_acm_product.settings');
      if (($this->getAssetType($asset) === 'video')
        && ($this->acmProductSettings->get('pause_videos_download'))) {
        $download = FALSE;
      }

      // Use pims/asset id for lock key.
      try {
        $file = $download ? $this->downloadAsset($asset, $sku->getSku()) : NULL;
      }
      catch (\Exception $e) {
        watchdog_exception('SkuAssetManager', $e);
      }

      if ($file instanceof FileInterface) {
        $this->fileUsage->add($file, $sku->getEntityTypeId(), $sku->getEntityTypeId(), $sku->id());

        $asset['drupal_uri'] = $file->getFileUri();
        $asset['fid'] = $file->id();
        $save = TRUE;
      }
      elseif ($file === 'blacklisted') {
        $save = TRUE;
      }

      if (empty($asset['fid'])) {
        unset($assets[$index]);
      }
    }

    if ($save) {
      $sku->get('attr_assets')->setValue(serialize($assets));
      $sku->save();

      $this->logger->notice('Downloaded new asset for sku @sku.', [
        '@sku' => $sku->getSku(),
      ]);
    }

    return array_filter($assets, fn($row) => !empty($row['fid']));
  }

  /**
   * Download asset for PIMS Data and store in Drupal.
   *
   * @param array $data
   *   PIMS Data.
   * @param string $sku
   *   SKU of asset.
   * @param string $asset_type
   *   Type of asset.
   *
   * @return \Drupal\file\FileInterface|null|string
   *   File entity if image download successful.
   */
  private function downloadPimsAsset(array &$data, string $sku, string $asset_type) {
    $file_data = NULL;
    $non_cli_image_download = AcqSkuConfig::get('non_cli_image_download');
    // Return if it is non CLI request AND if the config value for it is
    // disabled.
    if (PHP_SAPI !== 'cli' && !$non_cli_image_download) {
      return NULL;
    }
    // If image is blacklisted, block download.
    if (isset($data['blacklist_expiry']) && time() < $data['blacklist_expiry']) {
      return NULL;
    }

    $image_settings = $this->getImageSettings();
    $base_url = $image_settings->get('pims_base_url');
    $pims_directory = $image_settings->get('pims_directory');

    // Prepare the directory path.
    $directory = ($asset_type === 'video')
      ? 's3://product/assets/' . $asset_type . '/' . $sku . '/' . trim($data['path'], '/')
      : 'brand://assets-shared/' . trim($data['path'], '/');
    $target = $directory . DIRECTORY_SEPARATOR . $data['filename'];

    // Check if file already exists in the directory.
    $file = $this->getFileIfTargetExists($target);
    if ($file instanceof FileInterface) {
      return $file;
    }

    // Use URL from response if available.
    $url = $data['url'] ?? implode('/', [
      trim($base_url, '/'),
      trim($pims_directory, '/'),
      trim($data['path'], '/'),
      trim($data['filename'], '/'),
    ]);

    if (!$this->validateFileExtension($sku, $url)) {
      return FALSE;
    }

    // Download the file contents.
    try {
      $timeout = Settings::get('media_download_timeout_' . $asset_type, NULL);
      if (!isset($timeout)) {
        $timeout = Settings::get('media_download_timeout', 5);
      }
      $options = [
        'timeout' => $timeout,
      ];

      $file_stream = $this->downloadFile($url, $options);
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
      // @todo SAVE blacklist info in a way so it does not have dependency on SKU.
      // Blacklist this image URL to prevent subsequent downloads for 1 day.
      $data['blacklist_expiry'] = strtotime('+1 day');
      // Leave a message for developers to find out why this happened.
      $this->logger->error('Empty file detected during download, blacklisted for 1 day from now. File remote id: @remote_id, File URL: @url on SKU @sku. @trace', [
        '@url' => $url,
        '@sku' => $sku,
        '@remote_id' => $data['filename'],
        '@trace' => json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5), JSON_THROW_ON_ERROR),
      ]);

      return 'blacklisted';
    }

    // Check if image was blacklisted, remove it from blacklist.
    if (isset($data['blacklist_expiry'])) {
      unset($data['blacklist_expiry']);
    }

    // Prepare the directory.
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
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
   * @return \Drupal\file\FileInterface|null|string
   *   File from asset if available.
   *
   * @throws \Exception
   */
  private function downloadAsset(array &$asset, string $sku) {
    $lock_key = '';
    $type = $this->getAssetType($asset);

    // Allow disabling this through settings.
    if (Settings::get('media_avoid_parallel_downloads', 1)) {
      $id = $asset['pims_' . $type]['id'] ?? $asset['Data']['AssetId'];
      $lock_key = 'download_' . $type . '_' . $id;

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
    if (isset($asset['pims_' . $type]) && is_array($asset['pims_' . $type])) {
      $file = $this->downloadPimsAsset($asset['pims_' . $type], $sku, $type);
    }

    if ($file instanceof FileInterface) {
      if ($lock_key) {
        // Add file id in cache for other processes to be able to use.
        $this->cacheMediaFileMapping->set($lock_key, $file->id(), $this->time->getRequestTime() + 120);
      }

      $this->logger->notice('Downloaded or re-used file @fid, uri @uri for Asset @id', [
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
      // phpcs:ignore
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
    $image_settings = $this->getImageSettings();

    // Fetch weights of asset types based on the pagetype.
    $sku_asset_type_weights = $image_settings->get('weights')[$page_type];

    // Fetch angle config.
    $sort_angle_weights = $image_settings->get('weights')['angle'];

    // Create a multidimensional array of assets keyed by their asset type.
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

            $a_multi_pack = $a['Data']['IsMultiPack'] ?? NULL;
            $b_multi_pack = $b['Data']['IsMultiPack'] ?? NULL;
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

            // Finally, sort by Number.
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
   * Helper function to validate if the file extension is supported.
   *
   * Adds log message if unsupported file validation requested.
   *
   * @param string $sku
   *   SKU for logging.
   * @param string $url
   *   URL of the file to download.
   *
   * @return bool
   *   TRUE if supported.
   */
  private function validateFileExtension(string $sku, string $url) {
    // Using multiple function to get extension to avoid cases with query
    // string and hash in URLs.
    $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

    // Filter by extension only if value set for the setting.
    $allowed_extensions = Settings::get('allowed_product_extensions', NULL);
    if ($allowed_extensions && !in_array($extension, $allowed_extensions)) {
      $this->logger->warning('Skipping product media file because of unsupported extension. SKU: @sku, File: @file', [
        '@file' => $url,
        '@sku' => $sku,
      ]);

      return FALSE;
    }

    return TRUE;
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
    return (str_contains($asset['Data']['AssetType'], 'MovingMedia'))
      ? 'video'
      : 'image';
  }

  /**
   * Load or create file entity if target exists.
   *
   * @param string $target
   *   Target URI.
   *
   * @return \Drupal\file\FileInterface|null
   *   File entity if found.
   */
  protected function getFileIfTargetExists(string $target) {
    if (file_exists($target)) {
      // If file exists in directory, check if file entity exists.
      $files = $this->fileStorage->loadByProperties(['uri' => $target]);
      $file = reset($files);

      if (!($file instanceof FileInterface)) {
        // If file exists in directory but file entity doesnt exist
        // then create file entity.
        $file = File::create([
          'uri' => $target,
          'uid' => 0,
          'status' => FILE_STATUS_PERMANENT,
        ]);

        $file->save();
      }

      return $file ?? NULL;
    }
  }

  /**
   * Download Asset File.
   *
   * @param string $url
   *   Image URL.
   * @param array $request_options
   *   Request options.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   File stream.
   */
  protected function downloadFile(string $url, array $request_options = []) {
    $that = $this;

    $request_options['on_stats'] = function (TransferStats $stats) use ($that) {
      $code = ($stats->hasResponse())
        ? $stats->getResponse()->getStatusCode()
        : 0;

      $that->logger->notice(sprintf(
        'Asset download attempt finished for %s in %.4f. Response code: %d. Method: %s.',
        $stats->getEffectiveUri(),
        $stats->getTransferTime(),
        $code,
        $stats->getRequest()->getMethod()
      ));
    };

    return $this->httpClient->get($url, $request_options);
  }

  /**
   * Image settings.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Image settings config.
   */
  public function getImageSettings() {
    static $image_settings = NULL;
    $image_settings = is_null($image_settings)
      ? $this->configFactory->get('alshaya_media_assets.settings')
      : $image_settings;
    return $image_settings;
  }

}
