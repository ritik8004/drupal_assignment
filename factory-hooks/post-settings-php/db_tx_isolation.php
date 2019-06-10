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

$databases['default']['default']['init_commands']['isolation'] =  "SET SESSION tx_isolation='READ-COMMITTED'";

if (file_exists('/var/www/site-php')) {
  acquia_hosting_db_choose_active($conf['acquia_hosting_site_info']['db'], 'default', $databases, $conf);
}
