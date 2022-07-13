<?php

namespace Drupal\alshaya_egift_card\Breadcrumb;

use Drupal\alshaya_egift_card\Helper\EgiftCardHelper;
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
    // This breadcrumb should only be applicable for /egift-card/purchase.
    return $route_match->getRouteName() == 'alshaya_egift_card.egift_card_purchase';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));

    $landing_page_node_id = $this->eGiftCardHelper->egiftLandingPageNid();
    if (!empty($landing_page_node_id)) {
      // If eGift landing page exists then link to landing page
      $breadcrumb->addLink(Link::createFromRoute($this->t('eGift Card', [], ['context' => 'egift']), 'entity.node.canonical', ['node' => $landing_page_node_id], ['attributes' => ['class' => ['egift-brdcrb-nav']]]));
    }
    else {
      // If eGift landing page doesn't exist then link to the front page.
      $breadcrumb->addLink(Link::createFromRoute($this->t('eGift Card', [], ['context' => 'egift']), '<front>', [], ['attributes' => ['class' => ['egift-brdcrb-nav']]]));
    }

    $breadcrumb->addLink(Link::createFromRoute($this->t('Buy eGift Card', [], ['context' => 'egift']), 'alshaya_egift_card.egift_card_purchase', [], ['attributes' => ['class' => ['egift-brdcrb-nav']]]));
    $breadcrumb->addCacheableDependency(['url.path']);
    return $breadcrumb;
  }

}
