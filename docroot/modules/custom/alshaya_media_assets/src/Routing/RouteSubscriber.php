<?php

namespace Drupal\alshaya_media_assets\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class Route Subscriber.
 *
 * @package Drupal\alshaya_media_assets\Routing
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('alshaya_acm_product.product_settings_form')) {
      $route->setDefault('_form', '\Drupal\alshaya_media_assets\Form\AlshayaMediaAssetsProductSettingsForm');
    }
  }

}
