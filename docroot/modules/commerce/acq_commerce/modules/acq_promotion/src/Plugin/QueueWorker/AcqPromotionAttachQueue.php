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
    $promotion_attach_item = ['target_id' => $promotion_nid];
    $skus_not_found = [];

    foreach ($skus as $key => $sku) {
      $update_sku_flag = FALSE;
      $sku_entity = SKU::loadFromSku($sku['sku']);
      if ($sku_entity) {
        $sku_promotions = $sku_entity->get('field_acq_sku_promotions')->getValue();
        if (!in_array($promotion_attach_item, $sku_promotions, TRUE)) {
          $sku_entity->get('field_acq_sku_promotions')->appendItem($promotion_attach_item);
          $update_sku_flag = TRUE;
        }

        if ((isset($sku['final_price'])) && ($sku->final_price->value !== $sku['final_price'])) {
          $sku->final_price->value = $sku['final_price'];
          $update_sku_flag = TRUE;
        }

        if ($update_sku_flag) {
          $sku_entity->save();
        }
      }
      else {
        $skus_not_found[] = $sku['sku'];
      }
    }

    if (!empty($skus_not_found)) {
      $this->loggerFactory->get('acq_sku')->warning('Skus @skus not found in Drupal.',
        ['@skus' => implode(',', $skus_not_found)]);
    }

    $this->loggerFactory->get('acq_sku')->info('Attached Promotion:@promo to SKUs: @skus',
      ['@promo' => $promotion_nid, '@skus' => implode(',', $skus)]);
  }

}
