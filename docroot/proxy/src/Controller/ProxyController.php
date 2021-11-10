<?php

namespace App\Controller;

use Proxy\Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Laminas\Diactoros\ServerRequestFactory;
use GuzzleHttp\Exception\ClientException;

/**
 * The proxy controller.
 */
class ProxyController {

  /**
   * Proxy controller.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function proxy(Request $request) {
    $url = $this->getUrl($request);

    /** @var \Laminas\Diactoros\ServerRequest $request */
    $request = ServerRequestFactory::fromGlobals();

    // Create a guzzle client.
    $guzzle = new Client();

    // Create the proxy instance.
    $proxy = new Proxy(new GuzzleAdapter($guzzle));

    // Add a response filter that removes the encoding headers.
    $proxy->filter(new RemoveEncodingFilter());

    // Apply proxy filters.
    $proxy->filter(new ProxyFilter($url));

    try {
      // Forward the request and get the response.
      $response = $proxy->forward($request)->to($url);
    }
    catch (ClientException $e) {
      // In case of errors, return the error response.
      $response = $e->getResponse();
    }
    catch (\Exception $e) {
      // In case of other exception return exception response.
      $response = $e->getResponse();
    }

    return new Response(
      $response->getBody(),
      $response->getStatusCode(),
      $response->getHeaders()
    );
  }

  /**
   * Get API url.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return string
   *   The host from query string.
   */
  private function getUrl(Request $request) {
    return $request->get('url');
  }

}
