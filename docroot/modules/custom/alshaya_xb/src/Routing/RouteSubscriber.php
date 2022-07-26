<?php

namespace Drupal\alshaya_xb\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Return empty checkout page.
    if ($route = $collection->get('alshaya_spc.checkout')) {
      $route->setDefault('_controller', '\Drupal\alshaya_xb\Controller\CheckoutController::emptyPage');
    }
  }

}
