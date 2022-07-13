<?php

namespace App\Controller;

use App\Service\Config\SystemSettings;
use Proxy\Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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

    $settings = new SystemSettings(new RequestStack($request));

    /** @var \Laminas\Diactoros\ServerRequest $request */
    $request = ServerRequestFactory::fromGlobals();

    // Create a guzzle client.
    // Since proxy is used only on non-prod disabling SSL check.
    $guzzle = new Client(['verify' => FALSE]);

    // Create the proxy instance.
    $proxy = new Proxy(new GuzzleAdapter($guzzle));

    // Add a response filter that removes the encoding headers.
    $proxy->filter(new RemoveEncodingFilter());

    // Apply proxy filters.
    $proxy->filter(new ProxyFilter($url, $settings));

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
