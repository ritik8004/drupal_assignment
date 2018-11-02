<?php

namespace Drupal\acq_sku;

/**
 * Class AcqSkuEvents
 * @package Drupal\acq_sku
 */
final class AcqSkuEvents {

  /**
   * Event triggered once category sync is completed.
   *
   * @Event("Drupal\acq_sku\Events\AcqSkuSyncCatEvent")
   */
  const CAT_SYNC_COMPLETE = 'acq_sku.cat_sync_complete';
}
