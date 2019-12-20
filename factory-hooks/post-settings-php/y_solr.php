<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Implementation of ACSF post-settings-php hook to set index name for solr.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// Connect to Acquia Cloud Search server on Cloud.
if (isset($_ENV['AH_SITE_NAME'])) {
  $config['search_api.server.acquia_search_server']['name'] = 'Acquia Search API Solr server';
  $config['search_api.server.acquia_search_server']['backend_config']['connector'] = 'solr_acquia_connector';
}
// Connect to local solr server in local and travis.
else {
  $config['search_api.server.acquia_search_server']['name'] = 'Local Solr server';
  $config['search_api.server.acquia_search_server']['backend_config']['connector'] = 'standard';
  $config['search_api.server.acquia_search_server']['backend_config']['connector_config']['host'] = 'localhost';
  $config['search_api.server.acquia_search_server']['backend_config']['connector_config']['port'] = '8983';
  $config['search_api.server.acquia_search_server']['backend_config']['connector_config']['path'] = '/solr';
  $config['search_api.server.acquia_search_server']['backend_config']['connector_config']['core'] = '';
  $config['search_api.server.acquia_search_server']['backend_config']['connector_config']['commit_within'] = '1000';
}

