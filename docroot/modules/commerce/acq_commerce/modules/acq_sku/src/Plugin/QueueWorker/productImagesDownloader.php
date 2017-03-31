<?php

namespace Drupal\acq_sku\Plugin\QueueWorker;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\RequeueException;
use Drupal\Core\Queue\SuspendQueueException;

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
    // Get the magento base url and media dir prefix from config.
    $baseUrl = trim(\Drupal::config('acq_commerce.conductor')->get('base_url'), '/');
    $mediaPath = trim(\Drupal::config('acq_commerce.conductor')->get('media_path'), '/');

    // No need to process the queue if base url is not set.
    // Media path could be empty and files may come directly from base url.
    if (empty($baseUrl)) {
      throw new SuspendQueueException('Please set the magento base url first to allow downloading images');
    }

    // Prepare the media base url.
    $mediaBaseUrl = $baseUrl . '/';
    $mediaBaseUrl .= empty($mediaPath) ? '' : $mediaPath . '/';

    // Remove slash from beginning.
    $data['path'] = trim($data['path'], '/');

    // Prepare the full url for the file to download.
    $fileUrl = $mediaBaseUrl . $data['path'];

    // Preparing args for all info/error messages.
    $args = ['@file' => $fileUrl, '@sku_id' => $data['sku_id']];

    // Try to load the SKU entity.
    if ($sku = SKU::load($data['sku_id'])) {
      // Download the file contents.
      $fileData = file_get_contents($fileUrl);

      // Check to ensure errors like 404, 403, etc. are catched and empty file
      // not saved in SKU.
      if (empty($fileData)) {
        throw new \Exception(new FormattableMarkup('Failed to download file "@file" for SKU id @sku_id.', $args));
      }

      $fileName = basename($data['path']);
      $directory = 'public://acm/' . str_replace('/' . $fileName, '', $data['path']);

      // Prepare the directory.
      file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

      // Save the file as file entity.
      if ($file = file_save_data($fileData, $directory . '/' . $fileName, FILE_EXISTS_RENAME)) {
        $fileFieldValue = [
          'target_id' => $file->id(),
          'alt' => $sku->label(),
          'title' => $sku->label(),
        ];
        // File is saved, we set the file ID into image
        $sku->get($data['field'])->set($data['index'], $fileFieldValue);
        $sku->save();
        \Drupal::logger('acq_sku')->info('Saved file "@file" for SKU id @sku_id.', $args);
      }
      else {
        throw new RequeueException(new FormattableMarkup('Failed to save file "@file" for SKU id @sku_id.', $args));
      }
    }
    else {
      throw new \Exception(new FormattableMarkup('Failed to load SKU for SKU id @sku_id while saving file "@file".', $args));
    }
  }

}
