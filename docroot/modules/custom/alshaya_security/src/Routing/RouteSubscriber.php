<?php

namespace Drupal\alshaya_security\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Adds the 'rate_limited' tag to required routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Routes that will be rate limited on POST methods.
   *
   * @var array|string[]
   */
  private array $rateLimitedRoutes = [
    'user.pass',
    'user.pass.http',
  ];

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    foreach ($this->rateLimitedRoutes as $routeName) {
      if ($route = $collection->get($routeName)) {
        $route_tags = $route->getOption('tags') ?? [];
        // Adding rate_limited tag to required routes to avoid bot spamming.
        $route_tags[] = 'rate_limited';
        $route->setOption('tags', $route_tags);
        // Adding no cache option else routesubscriber won't trigger
        // before Drupal page_cache.
        $route->setOption('no_cache', 'TRUE');
      }
    }
  }

}
