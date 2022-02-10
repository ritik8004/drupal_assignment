<?php

/**
 * @file
 * Inc file.
 */

require __DIR__ . '/../../vendor/autoload.php';

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use GuzzleHttp\Client;

// See https://docs.acquia.com/acquia-cloud/develop/api/auth/
// for how to generate a client ID and Secret.
require_once 'credentials.php';

// phpcs:ignore
function invokeApi($api, $type = 'GET', $options = []) {
  global $_clientId, $_clientSecret;

  $provider = new GenericProvider([
    'clientId' => $_clientId,
    'clientSecret' => $_clientSecret,
    'urlAuthorize' => '',
    'urlAccessToken' => 'https://accounts.acquia.com/api/auth/oauth/token',
    'urlResourceOwnerDetails' => '',
  ]);

  try {
    // Try to get an access token using the client credentials grant.
    $accessToken = $provider->getAccessToken('client_credentials');

    // Generate a request object using the access token.
    $request = $provider->getAuthenticatedRequest(
      $type,
      'https://cloud.acquia.com/api/' . $api,
      $accessToken,
      $options
    );

    // Send the request.
    $client = new Client();
    $response = $client->send($request);

    $responseBody = $response->getBody();

    return $responseBody->getContents();
  }
  catch (IdentityProviderException $e) {
    // Failed to get the access token.
    exit($e->getMessage());
  }
}
