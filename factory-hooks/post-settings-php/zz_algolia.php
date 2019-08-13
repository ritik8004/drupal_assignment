<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook to set index name for algolia.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

global $_acsf_site_name;

// Ensure we never connect to Index of another ENV.
$config['search_api.index.acquia_search_index']['options']['algolia_index_name'] = $settings['env'] . '_' . $_acsf_site_name;
$config['block.block.autocompletewidgetofalgolia']['settings']['index'] = $settings['env'] . '_' . $_acsf_site_name;

// Values for developer machine here. This will need to be overridden in brand
// specific settings files on each env for each brand.
$settings['search_api.server.algolia']['backend_config']['application_id'] = 'testing24192T8KHZ';
$settings['search_api.server.algolia']['backend_config']['api_key'] = '628e74a9b6f3817cdd868278c8b8656e';
$settings['block.block.autocompletewidgetofalgolia']['settings']['application_id'] = 'testing24192T8KHZ';
$settings['block.block.autocompletewidgetofalgolia']['settings']['search_api_key'] = 'afeb84ab13757e11fbe8765142e2d7ad';
