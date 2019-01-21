<?php

/**
 * @file
 * Example implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Changing the database transaction isolation level from `REPEATABLE-READ`
// to `READ-COMMITTED` to avoid/minimize the deadlocks.
// @see https://support.acquia.com/hc/en-us/articles/360005253954-Fixing-database-deadlocks
// for reference.

// Only for the ACSF environment.
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $databases['default']['default']['init_commands'] = [
    'isolation' => "SET SESSION tx_isolation='READ-COMMITTED'",
  ];
  acquia_hosting_db_choose_active($conf['acquia_hosting_site_info']['db'], 'default', $databases, $conf);
}
