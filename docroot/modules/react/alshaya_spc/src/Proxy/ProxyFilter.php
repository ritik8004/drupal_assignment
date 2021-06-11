<?php

namespace Drupal\alshaya_spc\Proxy;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Proxy\Filter\FilterInterface;
use Laminas\Diactoros\Uri;
use Drupal\Component\Utility\UrlHelper;

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
   * ProxyFilter constructor.
   *
   * @param string $url
   *   The url of the endpoint.
   */
  public function __construct($url) {
    $this->url = $url;
  }

  /**
   * Invoke event.
   *
   * @inheritdoc
   */
  public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next) {
    // Collect arguments from query string.
    parse_str(parse_url($request->getUri()->getQuery(), PHP_URL_QUERY), $query);

    // Build uri with query string arguments.
    $uri = $this->url . '?' . http_build_query($query);

    if (UrlHelper::isValid($uri)) {
      // Do the request.
      $request = $request->withUri(new Uri($uri));
      $response = $next($request, $response);
    }
    else {
      throw new \Exception('The Url is invalid and may contain malicious content.');
    }

    return $response;
  }

}
