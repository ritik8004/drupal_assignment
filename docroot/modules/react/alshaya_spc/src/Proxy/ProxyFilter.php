<?php

namespace Drupal\alshaya_spc\Proxy;

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
   */
  protected $url;

  /**
   * ProxyFilter constructor.
   *
   * @param $url
   *    The url of the endpoint.
   */
  public function __construct($url) {
    $this->url = $url;
  }

  /**
   * @inheritdoc
   */
  public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
  {
    $request = $request->withUri(new Uri($this->url));
    $response = $next($request, $response);

    return $response;
  }
}
