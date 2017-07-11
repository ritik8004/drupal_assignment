<?php

namespace Drupal\acq_promotion\Plugin\QueueWorker;

use Drupal\acq_promotion\AlshayaPromotionQueueBase;

/**
 * Processes Skus to detach Promotions.
 *
 * @QueueWorker(
 *   id = "acq_promotion_detach_queue",
 *   title = @Translation("Acq Commerce Promotion detach queue"),
 * )
 */
class AcqPromotionDetachQueue extends AlshayaPromotionQueueBase {

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
    $promotion_node = $data['promotion'];

    foreach ($skus as $sku) {
      $this->logger->info("Sku being detached. " . $sku);
    }
  }

}
