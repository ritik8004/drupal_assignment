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
        if (strpos($sourceRoute->getPath(), '{color}') === FALSE) {
          $sourceRoute->setPath($sourceRoute->getPath() . '/{color}');
        }
        $sourceRoute->setDefault('color', '');
        $sourceRoute->setRequirement('color', '.*');
      }
    }
    catch (\Exception $e) {

    }
  }

}
