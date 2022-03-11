<?php

namespace Drupal\alshaya_egift_card\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Alshaya My Egift Card Breadcrumb.
 */
class AlshayaMyEgiftCardBreadcrumb implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * The current user service object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * AlshayaMyEgiftCardBreadcrumb constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current account object.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // This breadcrumb should only be applicable for /user/{user}/egift-card
    return $route_match->getRouteName() == 'alshaya_egift_card.my_egift_card';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    $user_id = $this->currentUser->id();
    $breadcrumb->addLink(Link::createFromRoute($this->t('My Account'), 'entity.user.canonical', ['user' => $user_id]));
    $breadcrumb->addLink(Link::createFromRoute($this->t('eGift Card', [], ['context' => 'egift']), 'alshaya_egift_card.my_egift_card', ['user' => $user_id], ['attributes' => ['class' => ['egift-brdcrb-nav']]]));
    $breadcrumb->addCacheableDependency(['url.path']);
    return $breadcrumb;
  }

}
