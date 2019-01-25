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
    $skus = $data['skus'];
    $promotion_nid = $data['promotion'];
    $promotion_attach_item = ['target_id' => $promotion_nid];
    $skus_not_found = [];
    $attached_skus = [];
    $invalidate_tags = ['node:' . $promotion_nid];

    foreach ($skus as $key => $sku) {
      $update_sku_flag = FALSE;
      $sku_entity = SKU::loadFromSku($sku['sku']);
      $sku_entity_translations = [];

      if ($sku_entity) {
        $translation_languages = $sku_entity->getTranslationLanguages(TRUE);

        $sku_promotions = $sku_entity->get('field_acq_sku_promotions')->getValue();
        if (!in_array($promotion_attach_item, $sku_promotions, TRUE)) {
          $sku_entity->get('field_acq_sku_promotions')->appendItem($promotion_attach_item);
          $update_sku_flag = TRUE;
        }

        // Update SKU translations if arabic translation is added to a promotion
        // post creation & import of that promo on Drupal.
        if (!empty($translation_languages)) {
          foreach ($translation_languages as $langcode => $lang_obj) {
            $sku_entity_translation = $sku_entity->getTranslation($langcode);
            $sku_promotions = $sku_entity_translation->get('field_acq_sku_promotions')->getValue();
            if (!in_array($promotion_attach_item, $sku_promotions, TRUE)) {
              $sku_entity_translation->get('field_acq_sku_promotions')->appendItem($promotion_attach_item);
              $sku_entity_translations[$langcode] = $sku_entity_translation;
              // Set an update translation flag per langcode & save sku entity
              // translation if corresponding langcode flag is set.
              $update_sku_translations_flag[$langcode] = TRUE;
            }
          }
        }

        if ((isset($sku['final_price'])) && ($sku_entity->final_price->value !== $sku['final_price'])) {
          $sku_entity->final_price->value = $sku['final_price'];

          // Update SKU final price.
          if (!empty($translation_languages)) {
            foreach ($sku_entity_translations as $langcode => $sku_entity_translation) {
              $sku_entity_translation->final_price->value = $sku['final_price'];
              $update_sku_translations_flag[$langcode] = TRUE;
            }
          }
          $update_sku_flag = TRUE;
        }

        if ($update_sku_flag) {
          $sku_entity->save();
        }

        // Process sku entity translations.
        if (!empty($sku_entity_translations)) {
          foreach ($sku_entity_translations as $langcode => $sku_entity_translation) {
            if (isset($update_sku_translations_flag[$langcode]) &&
              ($update_sku_translations_flag[$langcode])) {
              $sku_entity_translation->save();
            }
          }
        }
        $attached_skus[] = $sku['sku'];
        $invalidate_tags[] = 'acq_sku:' . $sku_entity->id();
      }
      else {
        $skus_not_found[] = $sku['sku'];
      }
    }

    // Invalidate sku cache tags & related promotion nid.
    Cache::invalidateTags($invalidate_tags);

    if (!empty($skus_not_found)) {
      $this->logger->warning('Skus @skus not found in Drupal.',
        ['@skus' => implode(',', $skus_not_found)]);
    }

    $this->logger->info('Attached Promotion:@promo to SKUs: @skus',
      ['@promo' => $promotion_nid, '@skus' => implode(',', $attached_skus)]);
  }

}
