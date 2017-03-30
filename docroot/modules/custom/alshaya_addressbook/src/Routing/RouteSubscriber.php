<?php

namespace Drupal\alshaya_addressbook\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // If route is for the 'address_book' type profile.
    if ($route = $collection->get('entity.profile.type.address_book.user_profile_form')) {
      $route->setDefault('_controller', '\Drupal\alshaya_addressbook\Controller\AlshayaProfileController::userProfileForm');
      $route->setDefault('_title_callback', '\Drupal\alshaya_addressbook\Controller\AlshayaProfileController::addPageTitle');
    }
  }

}
