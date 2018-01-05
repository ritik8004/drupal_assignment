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
    $promotion_type = $data['promotion_type'];
    $promotion_detach_item[] = ['target_id' => $promotion_nid];
    $invalidate_tags = ['node:' . $promotion_nid];
    foreach ($skus as $sku) {
      $sku_entity = SKU::loadFromSku($sku);
      $sku_promotions = $sku_entity->get('field_acq_sku_promotions')->getValue();

      $sku_promotions = array_udiff($sku_promotions, $promotion_detach_item, function ($array1, $array2) {
        return $array1['target_id'] - $array2['target_id'];
      });

      $sku_entity->get('field_acq_sku_promotions')->setValue($sku_promotions);
      $sku_entity->save();

      // Update Sku Translations.
      $translation_languages = $sku_entity->getTranslationLanguages(TRUE);

      if (!empty($translation_languages)) {
        foreach ($translation_languages as $langcode => $language) {
          $sku_entity_translation = $sku_entity->getTranslation($langcode);
          $sku_entity_translation->get('field_acq_sku_promotions')->setValue($sku_promotions);
          $sku_entity_translation->save();
        }
      }

      $invalidate_tags[] = 'acq_sku:' . $sku_entity->id();
    }

    $sku_texts = implode(',', $skus);

    // The skus detached from a catalog promotion are not part of those coming back during promotions sync.
    // So their final_price value does not get updated at that time. We thus need to resync these specific skus.
    if ($promotion_type === 'category' && $sku_texts) {
      foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
        $this->ingestApiWrapper->productFullSync($store_id, $langcode, $sku_texts);
      }
    }

    // Invalidate cache tags for updated skus & promotions.
    \Drupal::cache()->invalidateMultiple($invalidate_tags);

    $this->logger->info('Detached Promotion:@promo from SKUs: @skus',
      ['@promo' => $promotion_nid, '@skus' => $sku_texts]);
  }

}
