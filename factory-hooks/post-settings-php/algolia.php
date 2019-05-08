<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook to set index name for algolia.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// @TODO: Add different app ids for different brands here.
$config['search_api.index.acquia_search_index']['options']['algolia_index_name'] = $settings['env'];
