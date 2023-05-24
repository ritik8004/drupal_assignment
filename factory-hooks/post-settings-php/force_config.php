<?php

/**
 * @file
 * ACSF post-settings-php hook.
 *
 * Use this file to force any configurations at global level.
 *
 * @see https://docs.acquia.com/site-factory/extend/hooks/settings-php/
 *
 * phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis
 */

// Force disable database index as we no longer use it and cleanup
// will take a lot of time.
$config['search_api.index.product']['status'] = FALSE;
