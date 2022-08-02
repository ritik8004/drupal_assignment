<?php

namespace Drupal\alshaya_hello_member\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Alshaya hello member Benefit page breadcrumb Builder.
 */
class HelloMemberBenefitsPageBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * The current user service object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Request stack service object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * HelloMemberBenefitsPageBreadcrumbBuilder constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current account object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack service object.
   */
  public function __construct(AccountProxyInterface $current_user, RequestStack $request_stack) {
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
    $benefit_type = ($type === 'coupon') ? $this->t('Coupon', [], ['context' => 'hello_member']) : $this->t('Offer', [], ['context' => 'hello_member']);

    $breadcrumb->addLink(Link::createFromRoute($this->t('My Account'), 'entity.user.canonical', ['user' => $user_id]));
    $breadcrumb->addLink(Link::createFromRoute(
      $benefit_type,
      'alshaya_hello_member.hello_member_benefits_page',
      ['user' => $user_id, 'type' => $type, 'code' => $code],
      ['attributes' => ['class' => ['hello-member-brdcrb-nav']]]
    ));
    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

}
