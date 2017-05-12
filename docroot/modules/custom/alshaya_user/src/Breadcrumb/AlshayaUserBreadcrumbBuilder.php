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
    // Breadcrumb for the '' my-account' page.
    $routes = [
      'entity.user.canonical',
      'acq_customer.orders',
      'entity.user.edit_form',
      'entity.profile.type.address_book.user_profile_form',
      'alshaya_user.user_communication_preference',
      'change_pwd_page.change_password_form',
    ];
    return in_array($attributes->getRouteName(), $routes);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<none>'));
    $breadcrumb->addLink(Link::createFromRoute(t('My Account'), '<none>'));
    if ($route_match->getRouteName() != 'entity.user.canonical') {
      $request = \Drupal::request();
      $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
      $breadcrumb->addLink(Link::createFromRoute($title, '<none>'));
    }
    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

}
