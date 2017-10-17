<?php

namespace Drupal\alshaya_addressbook\Routing;

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
    // Change controller & title for address_book page.
    if ($route = $collection->get('entity.profile.type.user_profile_form')) {
      $route->setDefault('_title_callback', '\Drupal\alshaya_addressbook\Routing\RouteSubscriber::addressBookPageTitle');
      $route->setDefault('_controller', '\Drupal\alshaya_addressbook\Controller\AlshayaAddressBookController::userProfileForm');
    }
  }

  /**
   * Page title for address_book page.
   */
  public function addressBookPageTitle() {
    return t('Address book');
  }

}
