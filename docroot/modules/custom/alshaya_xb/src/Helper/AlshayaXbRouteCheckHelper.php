<?php

namespace Drupal\alshaya_xb\Helper;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Provides services to related to cross border feature.
 */
class AlshayaXbRouteCheckHelper {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteMatchInterface $route_match,
                              CurrentPathStack $current_path
                             ) {
    $this->routeMatch = $route_match;
    $this->currentPath = $current_path;
  }

  /**
   * Function to check whether the current route is eligible to add XB feature.
   *
   * @return bool
   *   Whether to add or not.
   */
  public function isCrossBorderRoute() {
    $current_path = $this->currentPath->getPath();
    if ((strpos($current_path, '/search') !== FALSE)
      || (strpos($current_path, '/checkout') !== FALSE)
      || (strpos($current_path, '/cart') !== FALSE)) {
      return TRUE;
    }

    $is_xb_route = FALSE;
    $route = $this->routeMatch->getRouteName();

    if ($route == 'entity.taxonomy_term.canonical') {
      $term = $this->routeMatch->getParameter('taxonomy_term');
      if ($term->bundle() === 'rcs_category' || $term->bundle() === 'acq_product_category') {
        $is_xb_route = TRUE;
      }
    }
    elseif ($route == 'entity.node.canonical') {
      $node = $this->routeMatch->getParameter('node');
      if ($node->bundle() === 'rcs_product' || $node->bundle() === 'acq_product') {
        $is_xb_route = TRUE;
      }
    }

    return $is_xb_route;
  }

}
