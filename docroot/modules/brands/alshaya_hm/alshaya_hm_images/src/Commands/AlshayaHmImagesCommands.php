<?php

namespace Drupal\alshaya_hm_images\Commands;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_hm_images\SkuAssetManager;
use Drupal\Core\Database\Connection;
use Drupal\file\FileInterface;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaHmImagesCommands.
 *
 * @package Drupal\alshaya_hm_images\Commands
 */
class AlshayaHmImagesCommands extends DrushCommands {

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Assets Manager.
   *
   * @var \Drupal\alshaya_hm_images\SkuAssetManager
   */
  private $assetsManager;

  /**
   * AlshayaHmImagesCommands constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\alshaya_hm_images\SkuAssetManager $assets_manager
   *   Assets Manager.
   */
  public function __construct(Connection $connection, SkuAssetManager $assets_manager) {
    $this->connection = $connection;
    $this->assetsManager = $assets_manager;
  }

  /**
   * Command to get faulty images of H&M.
   *
   * @param array $options
   *   Command options.
   *
   * @command alshaya_hm_images:generate-image-report
   *
   * @option check_faults List only gray images
   *
   * @aliases hmir,alshaya-hm-images-report
   */
  public function generateImageReport(array $options = [
    'check_faults' => 0,
    'faulty_size' => 24211,
    'batch_size' => 50,
  ]) {

    $faulty_size = (int) $options['faulty_size'];
    $check_faults = (bool) $options['check_faults'];
    $batch_size = (int) $options['batch_size'];

    $this->logger()->notice('Generating image report...');

    $filename = '/tmp/alshaya-hm-images_';
    if ($check_faults) {
      $filename .= 'faults_';
    }
    $filename .= $GLOBALS['acsf_site_name'] . '.log';

    // Clear existing data.
    $fp = fopen($filename, 'w');
    fclose($fp);

    $select = $this->connection->select('acq_sku_field_data');
    $select->fields('acq_sku_field_data', ['sku']);
    $select->condition('default_langcode', 1);
    $select->condition('type', 'simple');
    $select->condition('attr_assets__value', 'a:0:{}', '<>');
    $result = $select->execute()->fetchAll();

    $skus = array_column($result, 'sku');

    $batch = [
      'title' => 'Generate Image Report',
      'error_message' => 'Error occurred while generating image report, please check logs.',
    ];

    foreach (array_chunk($skus, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'generateImageReportChunk'],
        [$chunk, $filename, $check_faults, $faulty_size],
      ];
    }

    batch_set($batch);
    drush_backend_batch_process();

    $this->logger()->notice('Image reported generated and can be found at: ' . $filename);
  }

  /**
   * Generate image report for specific chunk.
   *
   * @param array $skus
   *   SKUs.
   * @param string $filename
   *   Report file name.
   * @param bool $check_faults
   *   Check faults or not.
   * @param int $faulty_size
   *   Faulty size.
   */
  public static function generateImageReportChunk(array $skus, string $filename, $check_faults, $faulty_size) {
    $fp = fopen($filename, 'a');

    /** @var \Drupal\alshaya_hm_images\SkuAssetManager $manager */
    $manager = \Drupal::service('alshaya_hm_images.skuassetsmanager');

    foreach ($skus as $sku) {
      $entity = SKU::loadFromSku($sku);
      if (!($entity instanceof SKU)) {
        continue;
      }

      $assets = $manager->getAssets($entity);
      $messages = [];
      foreach ($assets ?? [] as $asset) {
        if ((isset($asset['is_old_format']) && $asset['is_old_format']) || empty($asset['Data']['FilePath'])) {
          continue;
        }

        if (empty($asset['drupal_uri'])) {
          $messages[] = dt('Drupal was not able to download image for asset with id: @id for SKU: @sku, Has PIMS data: @pims', [
            '@id' => $asset['Data']['AssetId'],
            '@sku' => $sku,
            '@pims' => isset($asset['pims_image']) ? 'yes' : 'no',
          ]);

          continue;
        }

        $file_path = file_create_url($asset['drupal_uri']);

        if ($check_faults) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $file_path);
          curl_setopt($ch, CURLOPT_NOBODY, TRUE);
          curl_exec($ch);
          $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
          curl_close($ch);

          if ($size <= $faulty_size) {
            $messages[] = dt('Faulty asset found. Asset id: @id, SKU: @sku, File: @file, Has PIMS data: @pims', [
              '@id' => $asset['Data']['AssetId'],
              '@sku' => $sku,
              '@file' => $file_path,
              '@pims' => isset($asset['pims_image']) ? 'yes' : 'no',
            ]);

            continue;
          }
        }

        $messages[] = dt('Valid asset found. Asset id: @id, SKU: @sku, File: @file, Has PIMS data: @pims', [
          '@id' => $asset['Data']['AssetId'],
          '@sku' => $sku,
          '@file' => $file_path,
          '@pims' => isset($asset['pims_image']) ? 'yes' : 'no',
        ]);
      }

      if ($messages) {
        fwrite($fp, implode(PHP_EOL, $messages) . PHP_EOL);
      }
    }

    fclose($fp);
  }

  /**
   * Command to go through all the assets and find ones with corrupt data.
   *
   * It also marks them for re-downloading.
   *
   * @param array $options
   *   Command options.
   *
   * @command alshaya_hm_images:correct-corrupt-assets
   *
   * @option batch_size
   *   Batch size.
   * @option skus
   *   Comma separated list of skus to limit process to those skus.
   *
   * @usage drush hm-correct-corrupt-assets
   *   Process all the skus in system with drupal_uri in asset data.
   * @usage drush hm-correct-corrupt-assets --skus="sku1,sku2"
   *   Process all the skus specified in option --sku (separated by comma).
   *
   * @aliases hm-correct-corrupt-assets
   */
  public function correctCorruptAssets(array $options = ['batch_size' => 50, 'skus' => '']) {

    $batch_size = (int) $options['batch_size'];
    $skus = (string) $options['skus'];
    $skus = explode(',', $skus);

    $this->logger()->notice('Checking all assets...');

    $select = $this->connection->select('acq_sku_field_data');
    $select->fields('acq_sku_field_data', ['sku']);
    $select->condition('default_langcode', 1);
    $select->condition('type', 'simple');

    if ($skus) {
      $select->condition('sku', $skus, 'IN');
    }
    else {
      $select->condition('attr_assets__value', '%drupal_uri%', 'LIKE');
    }

    $result = $select->execute()->fetchAll();

    $skus = array_column($result, 'sku');

    $batch = [
      'title' => 'Process assets',
      'error_message' => 'Error occurred while processing assets, please check logs.',
    ];

    foreach (array_chunk($skus, $batch_size) as $chunk) {
      $batch['operations'][] = [
        [__CLASS__, 'correctCorruptAssetsChunk'],
        [$chunk],
      ];
    }

    batch_set($batch);
    drush_backend_batch_process();

    $this->logger()->notice('Processed all assets to find missing assets and download.');
  }

  /**
   * Batch callback.
   *
   * @param array $skus
   *   SKUs to process.
   */
  public static function correctCorruptAssetsChunk(array $skus) {
    $fileStorage = \Drupal::entityTypeManager()->getStorage('file');
    $logger = \Drupal::logger('AlshayaHmImagesCommands');
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');

    foreach ($skus as $sku_string) {
      $sku = SKU::loadFromSku($sku_string);
      if (!($sku instanceof SKU)) {
        continue;
      }

      $assets = unserialize($sku->get('attr_assets')->getString());

      $resave = FALSE;
      foreach ($assets ?? [] as $index => $asset) {
        // If drupal_uri is not set, we will let it be downloaded in
        // normal flow.
        if (empty($asset['drupal_uri'])) {
          continue;
        }

        $redownload = '';
        // If fid is empty, we have some issue, we will redownload.
        if (empty($asset['fid'])) {
          $redownload = 'missing fid';
        }
        else {

          $file = $fileStorage->load($asset['fid']);

          if ($file instanceof FileInterface) {
            $data = file_get_contents($file_system->realpath($file->getFileUri()));
            if (empty($data)) {
              $redownload = 'missing file';
            }
          }
          else {
            $redownload = 'missing file entity';
          }
        }

        if ($redownload) {
          $logger->error('Removing fid and/or drupal_uri from asset from @sku, for @reason, Asset: @asset.', [
            '@sku' => $sku->getSku(),
            '@reason' => $redownload,
            '@asset' => json_encode($asset),
          ]);

          $resave = TRUE;

          unset($asset['drupal_uri']);
          unset($asset['fid']);
          $assets[$index] = $asset;
        }
      }

      if ($resave) {
        $sku->get('attr_assets')->setValue(serialize($assets));
        $sku->save();
      }
    }
  }

}
