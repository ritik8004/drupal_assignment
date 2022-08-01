<?php

namespace Drupal\alshaya_hello_member\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Alshaya hello member Breadcrumb Builder.
 */
class AlshayaHelloMemberBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * The current user service object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Request stock service object.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * AlshayaHelloMemberBreadcrumbBuilder constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current account object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stock service object.
   */
  public function __construct(AccountInterface $current_user, RequestStack $request_stack) {
    $this->currentUser = $current_user;
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    // Breadcrumb for '/user/{uid}/hello-member-benefits/{type}/{code}'.
    return $attributes->getRouteName() == 'alshaya_hello_member.hello_member_benefits_page';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    $user_id = $this->currentUser->id();
    $type = $this->currentRequest->get('type');
    $code = $this->currentRequest->get('code');
    $benefit_type = ($type === 'coupon') ? 'Coupon' : 'Offer';

    $breadcrumb->addLink(Link::createFromRoute($this->t('My Account'), 'entity.user.canonical', ['user' => $user_id]));
    $breadcrumb->addLink(Link::createFromRoute($this->t('@benefit_type',
      ['@benefit_type' => $benefit_type],
      ['context' => 'hello_member']),
      'alshaya_hello_member.hello_member_benefits_page',
      ['user' => $user_id, 'type' => $type, 'code' => $code],
      ['attributes' => ['class' => ['hello-member-brdcrb-nav']]]
    ));
    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

}
