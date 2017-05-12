<?php

namespace Drupal\alshaya_acm\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Class AlshayaACMBreadcrumbBuilder.
 */
class AlshayaACMBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    // Breadcrumb for the '/cart' page.
    return $attributes->getRouteName() == 'acq_cart.cart';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<none>'));
    $breadcrumb->addLink(Link::createFromRoute(t('Basket'), '<none>'));
    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

}
