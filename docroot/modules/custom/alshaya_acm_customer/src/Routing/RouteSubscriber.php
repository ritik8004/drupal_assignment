<?php

namespace Drupal\alshaya_acm_customer\Routing;

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
    // Replace controller and method for orders list page.
    if ($route = $collection->get('acq_customer.orders')) {
      $route->setDefaults([
        '_controller' => '\Drupal\alshaya_acm_customer\Controller\CustomerController::listOrders',
        '_title' => 'Orders',
      ]);
    }
  }

}
