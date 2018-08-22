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
      'rest.csrftoken',
      'oauth2_token.token',
    ];

    // Allow some rest apis to work in maintenance mode.
    foreach ($collection->all() as $key => $route) {
      if (strpos($key, 'rest.acq_') === 0 || in_array($key, $rest_apis_to_allow)) {
        $route->setOption('_maintenance_access', TRUE);
      }
    }

    // Change the title of the cart page.
    if ($route = $collection->get('acq_cart.cart')) {
      $route->setDefault('_title', 'Basket');
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
