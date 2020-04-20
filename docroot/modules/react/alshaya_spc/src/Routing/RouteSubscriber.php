<?php

namespace Drupal\alshaya_spc\Routing;

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
    // Overriding the `cart` controller.
    if ($route = $collection->get('acq_cart.cart')) {
      $route->setDefaults([
        '_controller' => '\Drupal\alshaya_spc\Controller\AlshayaSpcController::cart',
        '_form' => NULL,
      ]);
    }
  }

}
