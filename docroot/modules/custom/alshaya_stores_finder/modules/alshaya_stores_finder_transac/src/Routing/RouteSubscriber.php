<?php

namespace Drupal\alshaya_stores_finder_transac\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Override store-list url to call API Controller.
    if ($route = $collection->get('alshaya_stores_finder.stores')) {
      $route->setDefaults([
        '_controller' => '\Drupal\alshaya_stores_finder_transac\Controller\AlshayaLocationsTransac::stores',
      ]);
    }
  }

}
