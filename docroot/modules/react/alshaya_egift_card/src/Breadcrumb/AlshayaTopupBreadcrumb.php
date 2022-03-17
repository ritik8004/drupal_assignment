<?php

namespace Drupal\alshaya_egift_card\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Alshaya Topup Card Breadcrumb.
 */
class AlshayaTopupBreadcrumb implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // This breadcrumb should only be applicable for /egift-card/topup.
    return $route_match->getRouteName() == 'alshaya_egift_card.topup_card';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    // @todo update route form config.
    $breadcrumb->addLink(Link::createFromRoute($this->t('eGift Card', [], ['context' => 'egift']), '<front>', [], ['attributes' => ['class' => ['egift-brdcrb-nav']]]));
    $breadcrumb->addLink(Link::createFromRoute($this->t('Top up eGift Card', [], ['context' => 'egift']), 'alshaya_egift_card.topup_card', [], ['attributes' => ['class' => ['egift-brdcrb-nav']]]));
    $breadcrumb->addCacheableDependency(['url.path']);
    return $breadcrumb;
  }

}
