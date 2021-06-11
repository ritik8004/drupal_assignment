<?php

namespace Drupal\alshaya_spc\Proxy;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Client;
use Laminas\Diactoros\ServerRequestFactory;
use Proxy\Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Proxy Controller.
 */
class ProxyController extends ControllerBase {

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Drupal Settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Proxy Controller Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The current request stack.
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings object.
   */
  public function __construct(RequestStack $request_stack,
                              Settings $settings) {
    $this->request = $request_stack->getCurrentRequest();
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('settings')
    );
  }

  /**
   * Proxy controller.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return ResponseInterface
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

    // Forward the request and get the response.
    return $proxy->forward($request)->to($url);
  }

  /**
   * Get API url;
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
