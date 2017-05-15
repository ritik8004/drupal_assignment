<?php

namespace Drupal\alshaya_user\Routing;

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
    // Change title_callback for my account page.
    if ($route = $collection->get('entity.user.canonical')) {
      $route->setDefault('_title_callback', '\Drupal\alshaya_user\Routing\RouteSubscriber::myAccountTitle');
    }
    // Change title for user edit form.
    if ($route = $collection->get('entity.user.edit_form')) {
      $route->setDefault('_title_callback', '\Drupal\alshaya_user\Routing\RouteSubscriber::editAccountTitle');
    }
    // Change title for change password form.
    if ($route = $collection->get('change_pwd_page.change_password_form')) {
      $route->setDefault('_title', 'Change Password');
    }
  }

  /**
   * Page title for My account page.
   */
  public function myAccountTitle() {
    return t('My Account');
  }

  /**
   * Page title for user edit page.
   */
  public function editAccountTitle() {
    return t('Contact Details');
  }

}
