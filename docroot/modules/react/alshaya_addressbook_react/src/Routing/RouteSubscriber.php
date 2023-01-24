<?php

namespace Drupal\alshaya_addressbook_react\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
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
    // Change controller & title for address_book page.
    if ($route = $collection->get('profile.user_page.multiple')) {
      $route->setDefault('_title_callback', '\Drupal\alshaya_addressbook_react\Controller\AlshayaAddressBookController::addressBookPageTitle');
      $route->setDefault('_controller', '\Drupal\alshaya_addressbook_react\Controller\AlshayaAddressBookController::userProfileForm');
      // Remove the access check as it's not required here.
      $route->setRequirements([
        '_role' => 'authenticated',
      ]);
    }
    else {
      // If the request is coming here then it means that profile module is not
      // enabled. So in that case we will have to make sure that we define the
      // backup route to support the address book.
      $route = new Route(
        // Path to attach this route to.
        '/user/{user}/address_book/list',
        // Route defaults params.
        [
          '_controller' => '\Drupal\alshaya_addressbook_react\Controller\AlshayaAddressBookController::userProfileForm',
          '_title_callback' => '\Drupal\alshaya_addressbook_react\Controller\AlshayaAddressBookController::addressBookPageTitle',
        ],
        [
          '_role' => 'authenticated',
        ],
      );

      // Add the backup route in the collection.
      $collection->add('profile.user_page.multiple', $route);
    }
  }

}
