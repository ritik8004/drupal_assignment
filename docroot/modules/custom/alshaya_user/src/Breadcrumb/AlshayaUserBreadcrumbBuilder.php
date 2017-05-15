<?php

namespace Drupal\alshaya_user\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Class AlshayaUserBreadcrumbBuilder.
 */
class AlshayaUserBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    // Breadcrumb for the 'my-account' page.
    $routes = $this->myAccountRoutes();
    return isset($routes[$attributes->getRouteName()]);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<front>'));
    $user_id = \Drupal::currentUser()->id();
    $breadcrumb->addLink(Link::createFromRoute(t('My Account'), 'entity.user.canonical', ['user' => $user_id]));
    if ($route_match->getRouteName() != 'entity.user.canonical') {
      $request = \Drupal::request();
      $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
      $breadcrumb->addLink(Link::createFromRoute($title, $route_match, $this->myAccountRoutes()[$route_match]));
    }
    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

  /**
   * My Account routes array.
   *
   * @return array
   *   Route array on my account page.
   */
  protected function myAccountRoutes() {
    $current_user_id = \Drupal::currentUser()->id();
    $routes = [
      'entity.user.canonical' => [
        'user' => $current_user_id,
      ],
      'acq_customer.orders' => [
        'orders' => $current_user_id,
      ],
      'entity.user.edit_form' => [
        'user' => $current_user_id,
      ],
      'entity.profile.type.address_book.user_profile_form' => [
        'user' => $current_user_id,
        'profile_type' => 'address_book',
      ],
      'alshaya_user.user_communication_preference' => [
        'user' => $current_user_id,
      ],
      'change_pwd_page.change_password_form' => [
        'user' => $current_user_id,
      ],
    ];

    return $routes;
  }

}
