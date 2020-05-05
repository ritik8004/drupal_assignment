<?php

namespace Drupal\alshaya_acm_checkoutcom\Routing;

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
    // Add access checking for Payments cards page.
    if ($route = $collection->get('acq_checkoutcom.payment_cards')) {
      $route->setRequirement('_custom_access', 'alshaya_acm_checkoutcom.payment_cards_page_access_check::access');
    }
  }

}
