<?php

namespace Drupal\alshaya_egift_card\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Alshaya My Egift Card Breadcrumb.
 */
class AlshayaMyEgiftCardBreadcrumb implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * The current user service object.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  public $currentUser;

  /**
   * AlshayaStaticPageBreadcrumbBuilder constructor.
   *
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current account object.
   */
  public function __construct(AccountProxy $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return $route_match->getRouteName() == 'alshaya_egift_card.my_account';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    $user_id = $this->currentUser->id();
    $breadcrumb->addLink(Link::createFromRoute($this->t('My Account'), 'entity.user.canonical', ['user' => $user_id]));
    $breadcrumb->addLink(Link::createFromRoute($this->t('Egift Card'), 'alshaya_egift_card.my_account'));
    $breadcrumb->addCacheableDependency(['url.path']);
    return $breadcrumb;
  }

}
