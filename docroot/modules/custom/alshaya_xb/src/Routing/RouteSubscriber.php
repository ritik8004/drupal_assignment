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
    $route = $collection->get('alshaya_spc.checkout');
    $route?->setDefault('_controller', '\Drupal\alshaya_xb\Controller\CheckoutController::checkoutPage');

    // Altering the access for profile.user_page.multiple route.
    $route = $collection->get('profile.user_page.multiple');
    $route?->setRequirement('_access', 'FALSE');
  }

}
