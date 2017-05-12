<?php

namespace Drupal\alshaya_department_page\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Class AlshayaDepartmentPageBreadcrumbBuilder.
 */
class AlshayaDepartmentPageBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Breadcrumb for department pages.
    return $route_match->getRouteName() == 'entity.node.canonical'
    && $route_match->getParameter('node')->bundle() == 'department_page';
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
