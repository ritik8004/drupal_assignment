<?php

/**
 * @file
 * Example implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

$env = 'local';

if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $env = $_ENV['AH_SITE_ENVIRONMENT'];
}
elseif (getenv('TRAVIS') || getenv('CI_BUILD_ID')) {
  $env = 'travis';
}

if (in_array($env, ['local', 'travis'])) {
  $config['search_api.server.non_transac_acquia_search_server']['name'] = 'Local SOLR Server';
  $config['search_api.server.non_transac_acquia_search_server']['backend_config']['connector_config']['host'] = 'localhost';
  $config['search_api.server.non_transac_acquia_search_server']['backend_config']['connector_config']['port'] = '8983';
  $config['search_api.server.non_transac_acquia_search_server']['backend_config']['connector_config']['path'] = '/solr';
  $config['search_api.server.non_transac_acquia_search_server']['backend_config']['connector_config']['core'] = '';
  $config['search_api.server.non_transac_acquia_search_server']['backend_config']['connector_config']['commit_within'] = 1000;
}
else {
  $config['search_api.server.non_transac_acquia_search_server']['name'] = 'Cloud SOLR Server';
  $config['search_api.server.non_transac_acquia_search_server']['backend_config']['connector'] = 'solr_acquia_connector';
}
