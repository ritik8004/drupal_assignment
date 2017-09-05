<?php

/**
 * @file
 * Master file, modify it to set env, credentials.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

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
 * @param array $options
 *   Options array.
 * @param int $store_id
 *   Store id.
 */
function invoke_api($api, $method = 'GET', array $options = [], $store_id = 1) {
  // $env = 'https://alshaya-uat.eu-west-1.prod.acm.acquia.io/v1/';
  // $env = 'https://alshaya-test.eu-west-1.prod.acm.acquia.io/v1/';
  $env = 'https://alshaya-dev.eu-west-1.prod.acm.acquia.io/v1/';

  $endpoint = $env . $api;

  $key = new Key('uAfqsl!BMf5xd8Z', 'eS#8&0@XyegNUO');
  $middleware = new HmacAuthMiddleware($key);

  // Register the middleware.
  $stack = HandlerStack::create();
  $stack->push($middleware);

  // Create a client.
  $client = new Client([
    'handler' => $stack,
  ]);

  $options['query']['store_id'] = $store_id;

  if ($method == 'POST') {
    $result = $client->post($endpoint, $options);
  }
  else {
    $result = $client->get($endpoint, $options);
  }

  print_r(json_decode($result->getBody()));
}
