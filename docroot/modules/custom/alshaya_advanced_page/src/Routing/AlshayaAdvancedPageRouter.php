<?php

namespace Drupal\alshaya_advanced_page\Routing;

use Drupal\Core\Routing\Router;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AlshayaAdvancedPageRouter.
 */
class AlshayaAdvancedPageRouter extends Router {

  /**
   * Uniquely identify the sub-request.
   *
   * @var bool
   */
  protected $isSubRequest = FALSE;

  /**
   * {@inheritdoc}
   */
  public function match($pathinfo) {
    $static = &drupal_static('alshaya_advanced_page_router_match', []);

    if (!isset($static[$pathinfo])) {
      // This is to uniquely identify the sub-request.
      $this->isSubRequest = TRUE;

      $request = Request::create($pathinfo);
      $static[$pathinfo] = $this->matchRequest($request);
    }

    return $static[$pathinfo];
  }

  /**
   * {@inheritdoc}
   */
  protected function matchCollection($pathinfo, RouteCollection $routes) {
    if ($route = $routes->get('entity.node.canonical')) {
      // Only if full page request.
      if (!$this->isSubRequest) {
        // @see AlshayaAdvancedPageRouteProvider::getRoutesByPath().
        if ($department_node = $route->getOption('_department_page_node')) {
          $pathinfo = '/node/' . $department_node;
        }
      }
    }

    // Let resume the original processing.
    return parent::matchCollection($pathinfo, $routes);
  }

}
