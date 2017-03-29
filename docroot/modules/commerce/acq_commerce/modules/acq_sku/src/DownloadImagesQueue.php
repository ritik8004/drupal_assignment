<?php

/**
 * @file
 * Wrapper to manage dowloading of product images..
 */

namespace Drupal\acq_sku;


use Drupal\Core\Queue\QueueInterface;

class DownloadImagesQueue {
  const QUEUE_ID = 'acq_sku_download_product_images';
  const BATCH_COUNT = 20;

  /**
   * @var QueueInterface
   */
  protected $queue = NULL;

  public function __construct() {
    $this->queue = \Drupal::service('queue')->get(static::QUEUE_ID);
  }

  /**
   * Clear the queue.
   */
  public function clear() {
    $this->queue->deleteQueue();
  }

  /**
   * @return mixed
   *   Number of items in the queue.
   */
  public function queueSize() {
    return $this->queue->numberOfItems();
  }

  /**
   * Add item to queue for processing.
   *
   * @param $sku_id
   *   SKU Entity ID to attach the image to.
   * @param $path
   *   Path of image.
   */
  public function addItem($sku_id, $path) {
    $this->queue->createItem([
      'sku_id' => $sku_id,
      'path' => $path,
    ]);
  }

}
