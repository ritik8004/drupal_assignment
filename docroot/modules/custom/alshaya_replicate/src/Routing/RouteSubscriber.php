<?php

namespace Drupal\alshaya_replicate\Routing;

/**
 * @file
 * Contains \Drupal\alshaya_replicate\Routing\RouteSubscriber.
 */

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('replicate_ui.settings')) {
      $route->setDefault('_form', '\Drupal\alshaya_replicate\Form\AlshayaReplicateUISettingsForm');
    }
  }

}
