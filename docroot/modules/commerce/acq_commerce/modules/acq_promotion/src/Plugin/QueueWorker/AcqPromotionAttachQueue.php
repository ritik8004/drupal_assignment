<?php

namespace Drupal\acq_promotion\Plugin\QueueWorker;

use Drupal\acq_promotion\AlshayaPromotionQueueBase;
use Drupal\acq_sku\Entity\SKU;
use Drupal\node\Entity\Node;

/**
 * Processes Skus to attach Promotions.
 *
 * @QueueWorker(
 *   id = "acq_promotion_attach_queue",
 *   title = @Translation("Acq Commerce Promotion attach queue"),
 * )
 */
class AcqPromotionAttachQueue extends AlshayaPromotionQueueBase {

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

    foreach ($skus as $key => $sku) {
      $sku_entity = SKU::loadFromSku($sku['sku']);
      $sku_promotions = $sku_entity->get('field_acq_sku_promotions')->getValue();
      $sku_promotions += $promotion_nid;
      $sku_entity->get('field_acq_sku_promotions')->setValue($sku_promotions);
      $sku->final_price->value = $sku['final_price'];
      $sku_entity->save();
    }
    $this->logger->info('Attached Promotion:@promo to SKUs: @skus',
      ['@promo' => $promotion_nid, '@skus' => implode(',', $skus)]);
  }

}
