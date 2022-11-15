<?php

namespace Drupal\alshaya_addressbook_react\Routing;

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
    // Change controller & title for address_book page.
    if ($route = $collection->get('profile.user_page.multiple')) {
      $route->setDefault('_title_callback', '\Drupal\alshaya_addressbook_react\Controller\AlshayaAddressBookController::addressBookPageTitle');
      $route->setDefault('_controller', '\Drupal\alshaya_addressbook_react\Controller\AlshayaAddressBookController::userProfileForm');
    }
  }

}
