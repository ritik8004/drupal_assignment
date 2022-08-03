<?php

namespace Drupal\alshaya_advanced_page\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Alshaya Advanced Page Event Subscriber.
 */
class AlshayaAdvancedPageEventSubscriber implements EventSubscriberInterface {

  /**
   * Route matcher.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * AlshayaAdvancedPageEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route matcher.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * Check for term route and change context to department page node.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function onRequest(GetResponseEvent $event) {
    // Lets not do anything for AJAX requests. We are facing issues because
    // route object gets added in static cache in route match when doing here.
    // We are not able to override request_stack in container if we use
    // \Drupal::getContainer()->set('request_stack') in code later during
    // execution it works fine if code below is not executed. Specifically
    // $this->routeMatch->getRouteObject(). Example:
    // Drupal\facets\Controller\FacetBlockAjaxController::ajaxFacetBlockView.
    $request = $event->getRequest();

    if (strpos(strtolower($request->getRequestUri()), 'ajax') > -1) {
      return;
    }

    $route = $this->routeMatch->getRouteObject();
    if ($route && $route->hasOption('_department_page_node')) {
      // This is to stop/override the redirect.
      // @see RouteNormalizerRequestSubscriber::onKernelRequestRedirect().
      // @see AlshayaAdvancedPageRouteProvider::getRoutesByPath().
      $request = $event->getRequest();
      $request->attributes->set('_disable_route_normalizer', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::REQUEST][] = ['onRequest', 30];
    return $events;
  }

}
