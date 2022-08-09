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
   * Request parameter to indicate that a request is a Drupal Ajax request.
   */
  public const AJAX_REQUEST_PARAMETER = '_drupal_ajax';

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
    // For Drupal AJAX request, we get paths like "en/home?_wrapper_format=html"
    // where there is no trailing slash at the end of the path. We don't want
    // such requests to get redirected, so we prevent further processing if
    // the request is an AJAX request.
    $is_ajax_request = !empty($event->getRequest()->request->get(static::AJAX_REQUEST_PARAMETER));
    if ($is_ajax_request) {
      return;
    }

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
    $events = [];
    // If priority is made higher than this, then the route match object returns
    // null.
    $events[KernelEvents::REQUEST][] = ['onKernelRequestRedirect', 32];
    return $events;
  }

}
