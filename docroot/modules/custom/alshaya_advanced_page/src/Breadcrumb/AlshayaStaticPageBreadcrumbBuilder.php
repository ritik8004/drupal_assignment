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
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<front>'));
    $request = \Drupal::request();
    $node = $route_match->getParameter('node');
    $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
    $breadcrumb->addLink(Link::createFromRoute($title, 'entity.node.canonical', ['node' => $node->id()]));
    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

}
