<?php

/**
 * @file
 * Implementation of ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Match the value from core.services.yml.
ini_set('session.gc_maxlifetime', 2_000_000);

// Disable session garbage collection, we do it via cron job.
ini_set('session.gc_probability', 0);
