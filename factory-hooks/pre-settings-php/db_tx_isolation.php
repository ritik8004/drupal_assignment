<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// This will stop the auto-connecting of database and in `post-settings` hook
// we changing the transaction isolation level.
// @see factory-hooks/post-settings-php/db_tx_isolation.php for more details.
$conf['acquia_hosting_settings_autoconnect'] = FALSE;
