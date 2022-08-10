<?php

namespace Drupal\alshaya_pdp_pretty_path\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Alter pdp routes, adding a parameter.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    try {
      $routeName = 'entity.node.canonical';
      $sourceRoute = $collection->get($routeName);

      if ($sourceRoute) {
        if (!str_contains($sourceRoute->getPath(), '{color}')) {
          $sourceRoute->setPath($sourceRoute->getPath() . '/{color}');
        }
        $sourceRoute->setDefault('color', '');
        $sourceRoute->setRequirement('color', '.*');
      }
    }
    catch (\Exception) {

    }
  }

}
