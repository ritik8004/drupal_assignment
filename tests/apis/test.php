<?php

/**
 * @file
 * Master file, modify it to set env, credentials.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../factory-hooks/environments/conductor.php';

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

/**
 * Function to invoke API.
 *
 * @param string $api
 *   API endpoint except the host and base path.
 * @param string $method
 *   Method.
 * @param array $data
 *   Data array.
 * @param int $store_id
 *   Store id.
 */
function invoke_api($api, $method = 'GET', array $data = [], $store_id = 1) {
  // Update this from envs available in conductor.php file.
  $env = 'hm_dev';

  // @codingStandardsIgnoreLine
  global $conductors;

  $endpoint = $conductors[$env]['url'] . $conductors[$env]['api_version'] . '/' . $api;

  $key = new Key($conductors[$env]['hmac_id'], base64_encode($conductors[$env]['hmac_secret']));
  $middleware = new HmacAuthMiddleware($key);

  // Register the middleware.
  $stack = HandlerStack::create();
  $stack->push($middleware);

  // Create a client.
  $client = new Client([
    'handler' => $stack,
    'headers' => [
      'X-ACM-UUID' => $store_id,
    ],
  ]);

  $options = [];
  $options['query']['store_id'] = $store_id;

  if ($method == 'POST') {
    $options['form_params'] = $data;
    $result = $client->post($endpoint, $options);
  }
  elseif ($method == 'JSON') {
    $options['json'] = $data;
    $result = $client->post($endpoint, $options);
  }
  else {
    if (is_array($data)) {
      $options['query'] += $data;
    }

    // To allow hmac sign to be verified properly we need them in asc order.
    ksort($options['query']);

    $result = $client->get($endpoint, $options);
  }

  print_r(json_decode($result->getBody()));
}
