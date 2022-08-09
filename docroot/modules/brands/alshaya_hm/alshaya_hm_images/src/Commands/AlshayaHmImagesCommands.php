<?php

namespace Drupal\alshaya_hm_images\Commands;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Database\Connection;
use Drush\Commands\DrushCommands;

/**
 * Contains Alshaya Hm Images Commands methods.
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
   * AlshayaHmImagesCommands constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
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
        [self::class, 'generateImageReportChunk'],
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

    /** @var \Drupal\alshaya_media_assets\Services\SkuAssetManager $manager */
    $manager = \Drupal::service('alshaya_media_assets.skuassetsmanager');

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

}
