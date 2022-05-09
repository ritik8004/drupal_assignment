<?php

namespace Drupal\alshaya_egift_card\Breadcrumb;

use Drupal\alshaya_egift_card\Helper\EgiftCardHelper;
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
   * Egift card helper.
   *
   * @var \Drupal\alshaya_egift_card\Helper\EgiftCardHelper
   */
  protected $eGiftCardHelper;

  /**
   * AlshayaMyEgiftCardBreadcrumb constructor.
   *
   * @param \Drupal\alshaya_egift_card\Helper\EgiftCardHelper $egift_helper
   *   The eGift helper.
   */
  public function __construct(EgiftCardHelper $egift_helper) {
    $this->eGiftCardHelper = $egift_helper;
  }

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

    // Set link for the landing page.
    $breadcrumb->addLink($this->eGiftCardHelper->getBreadcrumbLink());

    $breadcrumb->addLink(Link::createFromRoute($this->t('Top up eGift card', []), 'alshaya_egift_card.topup_card', [], ['attributes' => ['class' => ['egift-brdcrb-nav']]]));
    $breadcrumb->addCacheableDependency(['url.path']);
    return $breadcrumb;
  }

}
