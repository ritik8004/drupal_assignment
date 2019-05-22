<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook to set index name for algolia.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

global $acsf_site_name;

// Ensure we never connect to Index of another ENV.
$config['search_api.index.acquia_search_index']['options']['algolia_index_name'] = $acsf_site_name . '_' . $settings['env'];
$config['block.block.autocompletewidgetofalgolia']['settings']['index'] = $acsf_site_name . '_' . $settings['env'];

// Values for developer machine here. This will need to be overridden in brand
// specific settings files on each env for each brand.
$settings['search_api.server.algolia']['backend_config']['application_id'] = 'HGR051I5XN';
$settings['search_api.server.algolia']['backend_config']['api_key'] = '6fc229a5d5d0f0d9cc927184b2e4af3f';
$settings['block.block.autocompletewidgetofalgolia']['settings']['application_id'] = 'HGR051I5XN';
$settings['block.block.autocompletewidgetofalgolia']['settings']['search_api_key'] = 'a2fdc9d456e5e714d8b654dfe1d8aed8';
