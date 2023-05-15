<?php
// phpcs:ignoreFile

/**
 * @file
 * Provides some utility functions to create ACM configuration.
 */

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/settings.php';

/**
 * Function to invoke API.
 *
 * @param string $endpoint
 *   API endpoint except the host and base path.
 * @param string $method
 *   Method.
 * @param array $data
 *   Data array.
 *
 * @return \stdClass
 *   The data returned by the API.
 */
function invoke_api($endpoint, $method = 'GET', array $data = []) {
  global $config;
  $endpoint = $config['url'] . $endpoint;

  $key = new Key($config['hmac_key'], base64_encode($config['hmac_secret']));
  $middleware = new HmacAuthMiddleware($key);

  // Register the middleware.
  $stack = HandlerStack::create();
  $stack->push($middleware);

  // Create a client.
  $client = new Client([
    'handler' => $stack,
  ]);

  $options = [];

  try {
    if ($method == 'POST') {
      $options['json'] = $data;
      $result = $client->post($endpoint, $options);
    }
    elseif ($method == 'JSON') {
      $options['json'] = $data;
      $result = $client->post($endpoint, $options);
    }
    else {
      if (is_array($data)) {
        $options['query'] = $data;

        // To allow hmac sign to be verified properly we need them in asc order.
        ksort($options['query']);
      }

      $result = $client->get($endpoint, $options);
    }
  }
  catch (Exception $e) {
    global $mode;
    if ($mode && $mode !== 'report') {
      error_log($e->getMessage());
    }

    return new stdClass();
  }

  $data = json_decode($result->getBody(), null);

  return $data;
}

/**
 * Function to create a new site on ACM.
 *
 * @param string $name
 *   Name of the site to create.
 * @param string $description
 *   Description of the site to create.
 * @param int $org_uuid
 *   The organization id to create the site into.
 *
 * @return \stdClass
 *   The data returned by the API.
 */
function create_site($name, $description, $org_uuid = NULL) {
  if (empty($org_uuid)) {
    global $config;
    $org_uuid = $config['org_uuid'];
  }

  $data = [
    'name' => $name,
    'description' => $description,
    'org_uuid' => $org_uuid,
  ];

  return invoke_api('config/site/create', 'POST', $data);
}

/**
 * Function to return a site configuration from ACM.
 *
 * @param int $site_id
 *   Id of the site to fetch.
 *
 * @return \stdClass
 *   The data returned by the API.
 */
function get_site(int $site_id) {
  return invoke_api('config/site/' . $site_id, 'GET');
}

/**
 * Function to get total number of sites for current org on ACM.
 *
 * There is no way from ACM API to get a list of sites in current org so the
 * only way is to test all ids one by one. Given there is also no way to know
 * this is the last site, we need a mechanism to stop the process hence this is
 * the purpose of $max_gap variable. It will stop browsing after this amount of
 * invalid ids.
 *
 * @param int $max_gap
 *   Number of invalid ids before we decide we tested enough.
 *
 * @return int
 *   The total number of sites.
 */
function get_sites($max_gap = 50) {
  $id = 1;
  $count = 0;
  $gap = $max_gap;

  while ($gap > 0) {
    $site = get_site($id);

    if (isset($site->name)) {
      $count++;
      $gap = $max_gap;
    }
    else {
      $gap--;
    }

    $id++;
  }

  return $count;
}

/**
 * Function to create a new authentication config on ACM.
 *
 * @param string $name
 *   Name of the site to create.
 * @param string $description
 *   Description of the site to create.
 * @param int $site_id
 *   The site id to create the auth into.
 * @param string $client_id
 *   The client id to authenticate on the system.
 * @param string $client_secret
 *   The client secret to authenticate on the system.
 * @param string $token
 *   The token to authenticate on the system.
 * @param string $token_secret
 *   The token secret to authenticate on the system.
 *
 * @return \stdClass
 *   The data returned by the API.
 */
function create_auth($name, $description, $site_id, $client_id, $client_secret, $token = '', $token_secret = '') {
  $data = [
    'name' => $name,
    'desciption' => $description,
    'site_id' => $site_id,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'token' => $token,
    'token_secret' => $token_secret,
  ];

  return invoke_api('config/auth_detail/create', 'POST', $data);
}

/**
 * Function to create a new system on ACM.
 *
 * @param string $name
 *   Name of the site to create.
 * @param string $description
 *   Description of the site to create.
 * @param int $site_id
 *   The site id to create the auth into.
 * @param string $type
 *   The system type (magento|drupal).
 * @param string $url
 *   The url of the system.
 * @param string $uuid
 *   The unique id of the system.
 * @param string $auth_id
 *   The id of the auth to use on the system.
 *
 * @return \stdClass
 *   The data returned by the API.
 */
function create_system($name, $description, $site_id, $type, $url, $uuid, $auth_id) {
  $data = [
    'name' => $name,
    'description' => $description,
    'site_id' => $site_id,
    'type' => $type,
    'url' => $url,
    'uuid' => (string) $uuid,
    'skip_ssl' => FALSE,
    'auth_id' => $auth_id,
  ];

  return invoke_api('config/system/create', 'POST', $data);
}

/**
 * Wrapper function to get ACM System (Magento or Drupal).
 *
 * @param int $system_id
 *
 * @return \stdClass
 *   System.
 */
function get_system(int $system_id) {
  $endpoint = str_replace(':system_id', $system_id, 'config/system/:system_id');
  return invoke_api($endpoint, 'GET');
}

/**
 * Update ACM System (Magento or Drupal).
 *
 * @param array $data
 *
 * @return \stdClass
 *   Response.
 */
function update_system(array $data) {
  return invoke_api('config/system/update', 'POST', $data);
}

/**
 * Function to create a new mapping on ACM.
 *
 * @param string $name
 *   Name of the site to create.
 * @param string $description
 *   Description of the site to create.
 * @param int $site_id
 *   The site id to create the auth into.
 * @param int $backend_id
 *   The id of the backend system.
 * @param int $frontend_id
 *   The id of the frontend system.
 *
 * @return \stdClass
 *   The data returned by the API.
 */
function create_mapping($name, $description, $site_id, $backend_id, $frontend_id) {
  $data = [
    'name' => $name,
    'description' => $description,
    'site_id' => $site_id,
    'backend_id' => $backend_id,
    'frontend_id' => $frontend_id,
  ];

  return invoke_api('config/mapping/create', 'POST', $data);
}

/**
 * Function to get the queue count for a given site.
 *
 * @param $site_id
 *   The site id to get the queue count from.
 *
 * @return \stdClass
 *   The data returned by the API.
 */
function get_queue_total($site_id) {
  return invoke_api('config/site/' . $site_id . '/queue/total', 'GET');
}

/**
 * Function to pause/unpause the queue.
 *
 * @param $site_id
 *   The site id to get the queue count from.
 * @param $status
 *   The status to set for the queue.
 *
 * @return object
 *   The data returned by the API.
 */
function update_queue_status($site_id, bool $status = FALSE) {
  return invoke_api('config/site/' . $site_id . '/queue', 'GET', ['pause' => $status]);
}

/**
 * Function to purge the queue.
 *
 * @param $site_id
 *   The site id to get the queue count from.
 *
 * @return object
 *   The data returned by the API.
 */
function purge_queue($site_id) {
  return invoke_api('config/site/' . $site_id . '/queue/purge', 'POST');
}

/**
 * Wrapper function to get first and last values.
 *
 * @param string $conductor_env
 *   Array key from conductor.php file.
 *
 * @return array
 *   Array containing two required values.
 */
function get_brand_country_and_env(string $conductor_env): array {
  $info = explode('_', $conductor_env);

  // Get the first and last values.
  // Support cases like wekw_sit_dev2.
  $country_brand = $info[0];
  $base_env = end($info);

  return [$country_brand, $base_env];
}
