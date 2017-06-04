<?php

namespace Drupal\alshaya_seo\Breadcrumb;

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
    return $route_match->getRouteName() == 'alshaya_seo.sitemap';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute(t('Site map'), 'alshaya_seo.sitemap'));
    $breadcrumb->addCacheableDependency(['url.path']);
    return $breadcrumb;
  }

}
