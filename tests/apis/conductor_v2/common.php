<?php

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

const ACM_ORG_ID = 1;

const ACM_BASE_URL = 'https://api.eu-west-1.prod.acm.acquia.io/v2/';

const ACM_HMAC_KEY = '55f360f632174015aafeb460d7acf3ec';

const ACM_HMAC_SECRET = 'M2M3NWZlYjItMDM2Ny00';

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Function to invoke API.
 *
 * @param string $endpoint
 *   API endpoint except the host and base path.
 * @param string $method
 *   Method.
 * @param array $data
 *   Data array.
 * @param int $store_id
 *   Store id.
 */
function invoke_api($endpoint, $method = 'GET', array $data = []) {
  $endpoint = ACM_BASE_URL . $endpoint;

  $key = new Key(ACM_HMAC_KEY, base64_encode(ACM_HMAC_SECRET));
  $middleware = new HmacAuthMiddleware($key);

  // Register the middleware.
  $stack = HandlerStack::create();
  $stack->push($middleware);

  // Create a client.
  $client = new Client([
    'handler' => $stack,
  ]);

  $options = [];

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
    }

    // To allow hmac sign to be verified properly we need them in asc order.
    ksort($options['query']);

    try {
      $result = $client->get($endpoint, $options);
    }
    catch (Exception $e) {
      var_dump($options);
      exit;
    }

  }

  $data = json_decode($result->getBody());

  return $data;
}

function create_site($name, $description, $org_id = ACM_ORG_ID) {
  $data = [
    'name' => $name,
    'description' => $description,
    'org_id' => $org_id,
  ];

  //var_dump($data);

  return invoke_api('config/site/create', 'POST', $data);
}

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

  //var_dump($data);

  return invoke_api('config/auth_detail/create', 'POST', $data);
}

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

  //var_dump($data);

  return invoke_api('config/system/create', 'POST', $data);
}

function create_mapping($name, $description, $site_id, $backend_id, $frontend_id) {
  $data = [
    'name' => $name,
    'desciption' => $description,
    'site_id' => $site_id,
    'backend_id' => $backend_id,
    'frontend_id' => $frontend_id,
  ];

  return invoke_api('config/mapping/create', 'POST', $data);
}