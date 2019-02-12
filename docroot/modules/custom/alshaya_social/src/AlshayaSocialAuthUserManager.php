<?php

namespace Drupal\alshaya_social;

use Drupal\social_auth\SocialAuthUserManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Transliteration\PhpTransliteration;
use Drupal\Core\Utility\Token;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Contains all logic that is related to Drupal user management.
 */
class AlshayaSocialAuthUserManager extends SocialAuthUserManager {

  /**
   * The social auth user manger.
   *
   * @var \Drupal\social_auth\SocialAuthUserManager
   */
  protected $socialAuthManager;

  /**
   * AlshayaSocialAuthUserManager constructor.
   *
   * @param \Drupal\social_auth\SocialAuthUserManager $social_auth_manager
   *   The social auth user manger.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Used for accessing Drupal configuration.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Used for logging errors.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Used for dispatching social auth events.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Used for loading and creating Drupal user objects.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Used for access Drupal user field definitions.
   * @param \Drupal\Core\Utility\Token $token
   *   Used for token support in Drupal user picture directory.
   * @param \Drupal\Core\Transliteration\PhpTransliteration $transliteration
   *   Used for user picture directory and file transliteration.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   Used to check if route path exists.
   * @param Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   Used for reading data from and writing data to session.
   */
  public function __construct(
    SocialAuthUserManager $social_auth_manager,
    RequestStack $request_stack,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory,
    EventDispatcherInterface $event_dispatcher,
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager,
    Token $token,
    PhpTransliteration $transliteration,
    LanguageManagerInterface $language_manager,
    RouteProviderInterface $route_provider,
    SessionInterface $session
  ) {
    $this->socialAuthManager = $social_auth_manager;
    $this->request = $request_stack->getCurrentRequest();
    parent::__construct(
      $config_factory,
      $logger_factory,
      $event_dispatcher,
      $entity_type_manager,
      $entity_field_manager,
      $token,
      $transliteration,
      $language_manager,
      $route_provider,
      $session
    );
  }

  /**
   * Returns the Post Login Path.
   *
   * @return string
   *   Post Login Path to which the user would be redirected after login.
   */
  protected function getLoginPostPath() {
    $destination = $this->request->query->get('target');
    $post_login = $destination ?? $this->configFactory->get('social_auth.settings')->get('post_login');
    $routes = $this->routeProvider->getRoutesByNames([$post_login]);
    if (empty($routes)) {
      // Route does not exist so just redirect to path.
      return new RedirectResponse(Url::fromUserInput($post_login)->toString());
    }
    else {
      return $this->redirect($post_login);
    }
  }

}
