<?php

namespace Drupal\alshaya_acm\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Alshaya ACM Breadcrumb Builder.
 */
class AlshayaACMBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

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
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute($this->t('Basket'), 'acq_cart.cart'));
    $breadcrumb->addCacheableDependency(['url.path']);
    // For 'cart' page, route name and breadcrumb will be same for all users.
    // So caching cart page breadcrumb permanently.
    $breadcrumb->addCacheContexts(['route']);
    $breadcrumb->mergeCacheMaxAge(Cache::PERMANENT);

    return $breadcrumb;
  }

}
