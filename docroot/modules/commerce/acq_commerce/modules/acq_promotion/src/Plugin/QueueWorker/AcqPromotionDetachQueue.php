<?php

namespace Drupal\acq_promotion\Plugin\QueueWorker;

use Drupal\acq_commerce\Conductor\ConductorException;
use Drupal\acq_promotion\AcqPromotionQueueBase;
use Drupal\acq_sku\Entity\SKU;

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
    $promotion_detach_item[] = ['target_id' => $promotion_nid];

    foreach ($skus as $sku) {
      $sku_entity = SKU::loadFromSku($sku);
      $sku_promotions = $sku_entity->get('field_acq_sku_promotions')->getValue();

      $sku_promotions = array_udiff($sku_promotions, $promotion_detach_item, function ($array1, $array2) {
        return $array1['target_id'] - $array2['target_id'];
      });

      $sku_entity->get('field_acq_sku_promotions')->setValue($sku_promotions);
      $sku_entity->save();
    }

    $sku_texts = implode(',', $skus);
    $endpoint = $this->apiVersion . '/ingest/product/sync';

    $doReq = function ($client, $opt) use ($endpoint) {
      return $client->post($endpoint, $opt);
    };

    try {
      $this->tryIngestRequest($doReq, 'productFullSync', 'products', $sku_texts);
    }
    catch (ConductorException $e) {
    }

    $this->loggerFactory->get('acq_sku')->info('Detached Promotion:@promo from SKUs: @skus',
      ['@promo' => $promotion_nid, '@skus' => $sku_texts]);
  }

}
