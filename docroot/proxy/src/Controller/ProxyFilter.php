<?php

namespace App\Controller;

use App\Service\Config\SystemSettings;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Proxy\Filter\FilterInterface;
use Laminas\Diactoros\Uri;

/**
 * Updates the request Url.
 */
class ProxyFilter implements FilterInterface {

  /**
   * The endpoint url.
   *
   * @var string
   *   The url.
   */
  protected $url;

  /**
   * The System settings.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

  /**
   * ProxyFilter constructor.
   *
   * @param string $url
   *   The url of the endpoint.
   */
  public function __construct($url, SystemSettings $settings) {
    $this->url = $url;
    $this->settings = $settings;
  }

  /**
   * Invoke event.
   *
   * @inheritdoc
   */
  public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next) {
    // Get magento host from settings.
    $magento_host = $this->settings->getSettings('alshaya_api.settings')['magento_host'];

    // Check if url received by proxy has magento host,
    // If url is not magento host then return response with 404 code.
    if (!strstr($this->url, $magento_host)) {
      return $response->withStatus(404, 'Page not found');
    }

    // Collect arguments from query string.
    $query = $request->getQueryParams();

    // Remove the url parameter which is for host.
    unset($query['url']);

    // Build uri with remaining query string arguments.
    $uri = $this->url . '?' . http_build_query($query);

    // Do the request.
    $request = $request->withUri(new Uri($uri));
    $response = $next($request, $response);

    return $response;
  }

}
