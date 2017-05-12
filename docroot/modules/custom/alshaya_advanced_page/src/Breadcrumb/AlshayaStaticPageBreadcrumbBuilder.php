<?php

namespace Drupal\alshaya_advanced_page\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Class AlshayaStaticPageBreadcrumbBuilder.
 */
class AlshayaStaticPageBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Breadcrumb for static pages.
    $static_bundle = ['advanced_page', 'page'];
    return ($route_match->getRouteName() == 'entity.node.canonical'
    && (in_array($route_match->getParameter('node')->bundle(), $static_bundle)));
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<none>'));
    $request = \Drupal::request();
    $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
    $breadcrumb->addLink(Link::createFromRoute($title, '<none>'));
    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

}
