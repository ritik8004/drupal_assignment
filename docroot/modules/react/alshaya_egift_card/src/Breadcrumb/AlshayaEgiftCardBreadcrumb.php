<?php

namespace Drupal\alshaya_egift_card\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Alshaya Egift Card Breadcrumb.
 */
class AlshayaEgiftCardBreadcrumb implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // This breadcrumb should only be applicable for /egift-card/purchase.
    return $route_match->getRouteName() == 'alshaya_egift_card.egift_card_purchase';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    // @todo update route form config.
    $breadcrumb->addLink(Link::createFromRoute($this->t('Egift Card', [], ['context' => 'breadcrumb']), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute($this->t('Buy eGift Card', [], ['context' => 'breadcrumb']), 'alshaya_egift_card.egift_card_purchase'));
    $breadcrumb->addCacheableDependency(['url.path']);
    return $breadcrumb;
  }

}
