<?php

namespace Drupal\alshaya_facets_pretty_paths;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Utilty Class.
 */
class AlshayaFacetsPrettyPathsHelper {
  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * UserRecentOrders constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(RouteMatchInterface $route_match,
                              RequestStack $request_stack
  ) {
    $this->routeMatch = $route_match;
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * Encode url components according to given rules.
   *
   * @param string $element
   *   Raw element value.
   *
   * @return string
   *   Encoded element.
   */
  public static function encodeFacetUrlComponents($element) {
    // Convert to lowercase.
    $element = strtolower($element);

    // Convert spaces to '_'.
    $element = str_replace(' ', '_', $element);

    // Convert - in the facet value to '__'.
    $element = str_replace('-', '__', $element);

    return $element;

  }

  /**
   * Decode url components according to given rules.
   *
   * @param string $element
   *   Encoded element value.
   *
   * @return string
   *   Raw element.
   */
  public static function decodeFacetUrlComponents($element) {

    // Convert __ in the facet value to '-'.
    $element = str_replace('__', '-', $element);

    // Convert _ to spaces.
    $element = str_replace('_', ' ', $element);

    // Capitalize first letter.
    $element = ucwords($element);

    return $element;
  }

  /**
   * Get active facets from request or route.
   *
   * @return array
   *   Filter array.
   */
  public function getActiveFacetFilters() {
    $filters = '';
    if ($this->routeMatch->getParameter('facets_query')) {
      $filters = $this->routeMatch->getParameter('facets_query');
    }
    elseif ($this->routeMatch->getRouteName() === 'views.ajax') {
      $q = $this->currentRequest->query->get('q') ?? $this->currentRequest->query->get('facet_filter_url');
      if ($q) {
        $route_params = Url::fromUserInput($q)->getRouteParameters();
        if (isset($route_params['facets_query'])) {
          $filters = $route_params['facets_query'];
        }
      }
    }
    elseif (empty($filters) && strpos($this->currentRequest->getPathInfo(), "/--") !== FALSE) {
      $filters = substr($this->currentRequest->getPathInfo(), strpos($this->currentRequest->getPathInfo(), "/--") + 3);
    }

    return array_filter(explode('--', $filters));
  }

}
