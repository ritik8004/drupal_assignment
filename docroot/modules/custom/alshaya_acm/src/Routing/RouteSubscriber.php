<?php

namespace Drupal\alshaya_acm\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $rest_apis_to_allow = [
      'rest.‌acq_categorysync.POST',
      'acq_customer_delete.POST',
      '‌acq_productsync.POST',
    ];

    // Allow some rest apis to work in maintenance mode.
    foreach ($rest_apis_to_allow as $api) {
      if ($route = $collection->get($api)) {
        $route->setOption('_maintenance_access', TRUE);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -1];
    return $events;
  }

}
