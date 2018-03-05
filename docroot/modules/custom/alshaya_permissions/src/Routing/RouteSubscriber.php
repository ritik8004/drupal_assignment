<?php

namespace Drupal\alshaya_permissions\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Add permission.
    if ($route = $collection->get('user.admin_create')) {
      $route->setRequirement('_permission', $route->getRequirement('_permission') . '+alshaya create user');
    }
  }

}
