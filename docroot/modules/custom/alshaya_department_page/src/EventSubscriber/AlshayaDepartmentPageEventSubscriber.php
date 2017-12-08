<?php

namespace Drupal\alshaya_department_page\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlshayaDepartmentPageEventSubscriber.
 */
class AlshayaDepartmentPageEventSubscriber implements EventSubscriberInterface {

  /**
   * Route matcher.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * AlshayaDepartmentPageEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route matcher.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * Check for request.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function onRequest(GetResponseEvent $event) {
    // Only for the node routes.
    if ($this->routeMatch->getRouteName() == 'entity.node.canonical') {
      $route = $this->routeMatch->getRouteObject();
      // This is to stop/override the redirect.
      // @see RouteNormalizerRequestSubscriber::onKernelRequestRedirect().
      // @see AlshayaDepartmentPageRouter::matchCollection().
      if ($route && $route->hasOption('_is_department_page')) {
        $request = $event->getRequest();
        $request->attributes->set('_disable_route_normalizer', TRUE);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 30];
    return $events;
  }

}
