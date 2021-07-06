<?php

namespace Drupal\alshaya_seo_transac\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class AlshayaSeoRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Lookup the route object by its route_name.
    if ($route = $collection->get('social_auth_google.callback')) {
      // Override the GoogleAuth controller.
      $route->setDefault(
        '_controller',
        'Drupal\alshaya_seo_transac\Controller\AlshayaGoogleAuthController::callback'
      );
    }
    if ($route = $collection->get('social_auth_facebook.callback')) {
      // Override the FacebookAuth controller.
      $route->setDefault(
      '_controller',
      'Drupal\alshaya_seo_transac\Controller\AlshayaFBAuthController::callback'
      );
    }
  }

}
