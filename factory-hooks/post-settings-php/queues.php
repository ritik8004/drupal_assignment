<?php

/**
 * @file
 * Implementation of ACSF post-settings-php hook to set unique queue service.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$settings['queue_service_alshaya_process_product'] = 'queue_unique.database';
$settings['alshaya_invalidate_category_listing_cache'] = 'queue_unique.database';
