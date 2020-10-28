<?php

/**
 * @file
 * Implementation of ACSF post-settings-php hook to set unique queue service.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$settings['queue_service_alshaya_process_product'] = 'queue_unique.database';
$settings['queue_service_alshaya_invalidate_cache_tags'] = 'queue_unique.database';
$settings['queue_service_alshaya_invalidate_cache_tags_on_priority'] = 'queue_unique.database';
// Queue for processing category sync for push mode.
$settings['queue_service_category_sync'] = 'queue_unique.database';
