<?php

namespace Drupal\acq_promotion\Plugin\QueueWorker;

use Drupal\acq_promotion\AcqPromotionQueueBase;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\Cache;

/**
 * Processes Skus to attach Promotions.
 *
 * @QueueWorker(
 *   id = "acq_promotion_attach_queue",
 *   title = @Translation("Acq Commerce Promotion attach queue"),
 * )
 */
class AcqPromotionAttachQueue extends AcqPromotionQueueBase {

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
    $rows = $data['skus'];
    $skus = array_column($rows, 'sku');

    // Get attached promotions and final price.
    $query = $this->db->select('acq_sku_field_data', 'sku');
    $query->leftJoin('acq_sku__field_acq_sku_promotions', 'sku_promotions', 'sku.id = sku_promotions.entity_id AND sku.langcode = sku_promotions.langcode');
    $query->addField('sku', 'sku');
    $query->addField('sku', 'final_price');
    $query->addField('sku_promotions', 'field_acq_sku_promotions_target_id', 'promotion_id');
    $query->condition('sku.sku', $skus, 'IN');
    $query->condition('sku.default_langcode', 1);
    $result = $query->execute()->fetchAll();

    $existing = [];
    foreach ($result as $record) {
      $existing[$record->sku]['final_price'] = $record->final_price;

      // A product can have multiple promotions attached.
      $existing[$record->sku]['promotion_ids'][$record->promotion_id] = $record->promotion_id;
    }

    $promotion_nid = $data['promotion'];
    $promotion_attach_item = ['target_id' => $promotion_nid];

    $skus_not_found = [];
    $attached_skus = [];
    $skipped_skus = [];

    foreach ($rows as $sku) {
      if (!isset($existing[$sku['sku']])) {
        $skus_not_found[] = $sku['sku'];
        continue;
      }

      $has_final_price = !empty($sku['final_price']);

      $row = $existing[$sku['sku']];

      // If promotion already available
      // AND if no final price OR final price is same.
      if (in_array($promotion_nid, $row['promotion_ids'])
        && (!$has_final_price || $row['final_price'] == $sku['final_price'])) {
        $skipped_skus[] = $sku['sku'];
        continue;
      }

      // Load SKU only if update sku flag is true.
      $sku_entity = SKU::loadFromSku($sku['sku']);

      // Sanity check.
      if (!($sku_entity instanceof SKU)) {
        $skus_not_found[] = $sku['sku'];
        continue;
      }

      $sku_entity->get('field_acq_sku_promotions')->appendItem($promotion_attach_item);

      // Check again for final price.
      if (!empty($sku['final_price']) && ($sku_entity->get('final_price')->getString() != $sku['final_price'])) {
        $sku_entity->get('final_price')->setValue($sku['final_price']);
      }

      $sku_entity->save();
      $attached_skus[] = $sku['sku'];
    }

    // Invalidate promotion cache.
    Cache::invalidateTags(['node:' . $promotion_nid]);

    $this->logger->notice('Processed acq_promotion_attach_queue queue. Attached: @attached; Skipped: @skipped; Not found: @notfound', [
      '@attached' => implode(',', $attached_skus),
      '@skipped' => implode(',', $skipped_skus),
      '@notfound' => implode(',', $skus_not_found),
    ]);
  }

}
