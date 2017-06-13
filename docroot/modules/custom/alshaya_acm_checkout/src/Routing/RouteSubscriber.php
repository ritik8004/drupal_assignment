<?php

namespace Drupal\alshaya_acm_checkout\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Change title_callback for checkout mulisteps pages.
    if ($route = $collection->get('acq_checkout.form')) {
      $route->setDefault('_title_callback', '\Drupal\alshaya_acm_checkout\CheckoutPaneTitles::checkoutPageTitle');
    }
  }

}
