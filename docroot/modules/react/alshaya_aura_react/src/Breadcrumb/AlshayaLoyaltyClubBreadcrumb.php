<?php

namespace Drupal\alshaya_aura_react\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Alshaya Loyalty Club Breadcrumb.
 */
class AlshayaLoyaltyClubBreadcrumb implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return $route_match->getRouteName() == 'alshaya_aura_react.my_loyalty_club';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute($this->t('About Aura'), 'alshaya_aura_react.my_loyalty_club'));
    $breadcrumb->addCacheableDependency(['url.path']);
    return $breadcrumb;
  }

}
