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
    // Regex to check for the '/taxonomy/term/{tid}'.
    $regex = "#^/taxonomy/term/(?P<term>\d+)$#su";
    // If matches.
    if (preg_match($regex, $pathinfo, $matches)) {
      if (isset($matches['term']) && is_numeric($matches['term'])) {
        // If department page exists.
        if ($department_node = alshaya_department_page_is_department_page($matches['term'])) {
          // If route object is there with node canonical.
          if ($route = $routes->get('entity.node.canonical')) {
            $pathinfo = '/node/' . $department_node->id();
            // Setting this to identify that this is department page coming
            // from the term page.
            // @see AlshayaDepartmentPageEventSubscriber::onRequest().
            $route->setOption('_is_department_page', TRUE);
          }
        }
      }
    }

    // Let resume the original processing.
    return parent::matchCollection($pathinfo, $routes);
  }

}
