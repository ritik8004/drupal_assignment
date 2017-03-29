<?php

namespace Drupal\acq_sku\Plugin\QueueWorker;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\RequeueException;

/**
 * Download images.
 *
 * @QueueWorker(
 *   id = "acq_sku_download_product_images",
 *   title = @Translation("Product images downloader"),
 *   cron = {"time" = 60}
 * )
 */
class ProductImagesDownloader extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $mediaBaseUrl = \Drupal::config('acq_commerce.conductor')->get('media_base_url');

    if (empty($mediaBaseUrl)) {
      throw new RequeueException('Please set the media base url first to download images');
    }

    $args = ['%file' => $data['path'], '%sku_id' => $data['sku_id']];

    if (0 && $sku = SKU::load($data['sku_id'])) {
      // Remove slash from beginning.
      $data['path'] = trim($data['path'], '/');

      // Save Image in local from remote data.
      $fileData = file_get_contents($mediaBaseUrl . $data['path']);

      if (empty($fileData)) {
        throw new \Exception(new FormattableMarkup('Failed to download file "%file" for SKU id %sku_id.', $args));
      }

      $fileName = basename($data['path']);
      $directory = 'public://' . str_replace('/' . $fileName, '', $data['path']);

      // Prepare the directory
      file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

      if ($file = file_save_data($fileData, $directory . '/' . $fileName, FILE_EXISTS_RENAME)) {
        $sku->set('image', $file->id());
        $sku->save();
        \Drupal::logger('acq_sku')->info('Saved file "%file" for SKU id %sku_id.', ['%file' => $data['path'], '%sku_id' => $data['sku_id']]);
      }
      else {
        throw new \Exception(new FormattableMarkup('Failed to save file "%file" for SKU id %sku_id.', $args));
      }
    }
    else {
      throw new \Exception(new FormattableMarkup('Failed to load SKU for SKU id %sku_id while saving file "%file".', $args));
    }
  }

}
