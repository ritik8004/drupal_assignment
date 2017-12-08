<?php

namespace Drupal\alshaya_department_page\Routing;

use Drupal\Core\Routing\Router;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class AlshayaDepartmentPageRouter.
 */
class AlshayaDepartmentPageRouter extends Router {

  /**
   * {@inheritdoc}
   */
  protected function matchCollection($pathinfo, RouteCollection $routes) {
    if ($route = $routes->get('entity.node.canonical')) {
      // @see AlshayaDepartmentPageRouteProvider::getRoutesByPath().
      if ($department_node = $route->getOption('_department_page_node')) {
        $pathinfo = '/node/' . $department_node;
      }
    }

    // Let resume the original processing.
    return parent::matchCollection($pathinfo, $routes);
  }

}
