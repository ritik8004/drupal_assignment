<?php

namespace Drupal\alshaya_social_facebook\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class AlshayaSocialFacebookRouteSubscriber.
 *
 * @package Drupal\alshaya_social_facebook\Routing
 */
class AlshayaSocialFacebookRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Change controller.
    // When user used login link on checkout process, redirect user back to
    // checkout delivery page/ Checkout login page (on error) when user
    // return back from facebook.
    $collection
      ->get('social_auth_facebook.callback')
      ->setDefault('_controller', '\Drupal\alshaya_social_facebook\Controller\AlshayaFacebookAuthController::callback');
  }

}
