<?php

namespace App\Controller;

use App\Service\Config\SystemSettings;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Proxy\Filter\FilterInterface;
use Laminas\Diactoros\Uri;
use Laminas\Diactoros\ResponseFactory;

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
   * @param \App\Service\Config\SystemSettings $settings
   *   The settings object.
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
  public function __invoke(RequestInterface $request, callable $next = NULL) : ResponseInterface {
    // Get magento urls from settings.
    $magento_urls = $this->settings->getAllMagentoUrls();

    // Check if url received by proxy has magento host.
    $has_magento_host = FALSE;
    foreach ($magento_urls as $magento_url) {
      if (str_starts_with($this->url, (string) $magento_url)) {
        $has_magento_host = TRUE;
        break;
      }
    }

    // If url does not have magento host then return response with 404 code.
    if (!$has_magento_host) {
      return (new ResponseFactory())
        ->createResponse(404, 'Page not found');
    }

    // Collect arguments from query string.
    $query = $request->getQueryParams();

    // Remove the url parameter which is for host.
    unset($query['url']);

    // Build uri with remaining query string arguments.
    $uri = $this->url . '?' . http_build_query($query);

    // Do the request.
    $request = $request->withUri(new Uri($uri));

    return $next($request);
  }

}
