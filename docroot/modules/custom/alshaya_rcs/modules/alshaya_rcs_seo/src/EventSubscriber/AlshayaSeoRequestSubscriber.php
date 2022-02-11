<?php

namespace Drupal\alshaya_rcs_seo\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Perform redirection on PLP if trailing slash not present.
 */
class AlshayaSeoRequestSubscriber implements EventSubscriberInterface {

  /**
   * Route matcher.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a AlshayaSeoRequestSubscriber object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route matcher.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * Performs a redirect if trailing slash not present.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestRedirect(GetResponseEvent $event) {
    $route_name = $this->routeMatch->getRouteName();
    $request = $event->getRequest();
    $request_path = $request->getPathInfo();

    $redirect_routes = ['entity.taxonomy_term.canonical', 'alshaya_master.home'];
    $is_promotion_page = ($route_name == 'entity.node.canonical' && $this->routeMatch->getParameter('node')->bundle() == 'rcs_promotion');
    if (in_array($route_name, $redirect_routes) || $is_promotion_page) {
      if (substr($request_path, -1) != '/') {
        $request_uri = $request_path . '/';
        $response = new RedirectResponse($request_uri, 301);
        $response->headers->set('cache-control', 'must-revalidate, no-cache, no-store, private');
        $event->setResponse($response);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // If priority is made higher than this, then the route match object returns
    // null.
    $events[KernelEvents::REQUEST][] = ['onKernelRequestRedirect', 32];
    return $events;
  }

}
