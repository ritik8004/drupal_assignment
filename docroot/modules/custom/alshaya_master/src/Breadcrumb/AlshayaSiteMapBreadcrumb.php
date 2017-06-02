<?php

namespace Drupal\alshaya_master\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Class AlshayaSiteMapBreadcrumb.
 */
class AlshayaSiteMapBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return $route_match->getRouteName() == 'alshaya_master.sitemap';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute(t('Site map'), 'alshaya_master.sitemap'));
    $breadcrumb->addCacheableDependency(['url.path']);
    return $breadcrumb;
  }

}
