<?php

namespace Drupal\alshaya_user\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AlshayaUserBreadcrumbBuilder.
 */
class AlshayaUserBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * The current user service object.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  public $currentUser;

  /**
   * The Title Resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Request stock service object.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * AlshayaStaticPageBreadcrumbBuilder constructor.
   *
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current account object.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The Title Resolver.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stock service object.
   */
  public function __construct(AccountProxy $current_user,
                              TitleResolverInterface $title_resolver,
                              RequestStack $request_stack) {
    $this->currentUser = $current_user;
    $this->titleResolver = $title_resolver;
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    // Breadcrumb for the 'my-account' page.
    $routes = $this->myAccountRoutes();
    return isset($routes[$attributes->getRouteName()]);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    $user_id = $this->currentUser->id();
    $breadcrumb->addLink(Link::createFromRoute($this->t('My Account'), 'entity.user.canonical', ['user' => $user_id]));
    if ($route_match->getRouteName() != 'entity.user.canonical') {
      $title = $this->titleResolver->getTitle($this->currentRequest, $route_match->getRouteObject());
      $breadcrumb->addLink(Link::createFromRoute($title, $route_match->getRouteName(), $this->myAccountRoutes()[$route_match->getRouteName()]));
    }
    $breadcrumb->addCacheableDependency(['url.path']);

    return $breadcrumb;
  }

  /**
   * My Account routes array.
   *
   * @return array
   *   Route array on my account page.
   */
  protected function myAccountRoutes() {
    $current_user_id = $this->currentUser->id();
    $routes = [
      'entity.user.canonical' => [
        'user' => $current_user_id,
      ],
      'acq_customer.orders' => [
        'user' => $current_user_id,
      ],
      'entity.user.edit_form' => [
        'user' => $current_user_id,
      ],
      'entity.profile.type.user_profile_form' => [
        'user' => $current_user_id,
        'profile_type' => 'address_book',
      ],
      'alshaya_user.user_communication_preference' => [
        'user' => $current_user_id,
      ],
      'change_pwd_page.change_password_form' => [
        'user' => $current_user_id,
      ],
      'acq_checkoutcom.payment_cards' => [
        'user' => $current_user_id,
      ],
    ];

    return $routes;
  }

}
