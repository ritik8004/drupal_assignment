<?php
namespace Drupal\alshaya_search\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class FacetAjaxRouteSubscriber extends RouteSubscriberBase {
  /**
   * Alters existing routes for a specific collection.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('facets.block.ajax')) {
      $route->setDefault('_controller', '\Drupal\alshaya_search\Controller\AlshayaSearchAjaxController::ajaxFacetBlockView');
    }
  }
}