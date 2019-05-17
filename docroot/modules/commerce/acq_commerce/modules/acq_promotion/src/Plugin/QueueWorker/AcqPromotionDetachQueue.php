<?php

namespace Drupal\acq_promotion\Plugin\QueueWorker;

use Drupal\acq_promotion\AcqPromotionQueueBase;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\Cache;

/**
 * Processes Skus to detach Promotions.
 *
 * @QueueWorker(
 *   id = "acq_promotion_detach_queue",
 *   title = @Translation("Acq Commerce Promotion detach queue"),
 * )
 */
class AcqPromotionDetachQueue extends AcqPromotionQueueBase {

  /**
   * Works on a single queue item.
   *
   * @param mixed $data
   *   The data that was passed to
   *   \Drupal\Core\Queue\QueueInterface::createItem() when the item was queued.
   *
   * @throws \Drupal\Core\Queue\RequeueException
   *   Processing is not yet finished. This will allow another process to claim
   *   the item immediately.
   * @throws \Exception
   *   A QueueWorker plugin may throw an exception to indicate there was a
   *   problem. The cron process will log the exception, and leave the item in
   *   the queue to be processed again later.
   * @throws \Drupal\Core\Queue\SuspendQueueException
   *   More specifically, a SuspendQueueException should be thrown when a
   *   QueueWorker plugin is aware that the problem will affect all subsequent
   *   workers of its queue. For example, a callback that makes HTTP requests
   *   may find that the remote server is not responding. The cron process will
   *   behave as with a normal Exception, and in addition will not attempt to
   *   process further items from the current item's queue during the current
   *   cron run.
   *
   * @see \Drupal\Core\Cron::processQueues()
   */
  public function processItem($data) {
    $skus = $data['skus'];
    $promotion_nid = $data['promotion'];
    $detached_skus = [];
    $unprocessed_skus = [];

    foreach ($skus as $sku) {
      $sku_entity = SKU::loadFromSku($sku);

      // Check if the SKU added to queue is available before processing.
      if ($sku_entity instanceof SKU) {
        $this->promotionManager->removeOrphanPromotionFromSku($sku_entity, $promotion_nid);
        $detached_skus[] = $sku;
      }
      else {
        $unprocessed_skus[] = $sku;
      }
    }

    // Invalidate cache tags for promotion.
    Cache::invalidateTags(['node:' . $promotion_nid]);

    $this->logger->notice('Detached Promotion: @promo from SKUs: @skus', [
      '@promo' => $promotion_nid,
      '@skus' => implode(',', $detached_skus),
    ]);

    // Log unprocessed SKUs while detatching from Promotion.
    if (!empty($unprocessed_skus)) {
      $this->logger->notice('SKUs @skus not found while detaching from promotion: @promo', [
        '@promo' => $promotion_nid,
        '@skus' => implode(',', $unprocessed_skus),
      ]);
    }
  }

}
