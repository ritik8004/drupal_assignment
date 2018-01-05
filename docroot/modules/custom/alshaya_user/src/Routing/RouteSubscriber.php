<?php

namespace Drupal\alshaya_user\Routing;

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
    return $this->t('My Account');
  }

  /**
   * Page title for user edit page.
   */
  public function editAccountTitle() {
    return $this->t('Contact Details');
  }

}
