<?php

namespace Drupal\alshaya_social\EventSubscriber;

use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\social_auth\Event\ProviderErrorEvent;
use Drupal\social_auth\Event\ProviderRedirectEvent;
use Drupal\social_auth\Event\SocialAuthEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;

/**
 * Subscriber to set login_error_destination link and redirect on error.
 */
class AlshayaSocialEventSubscriber implements EventSubscriberInterface {

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Used to access GET parameters.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * AlshayaSocialEventSubscriber constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Used to access GET parameters.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory object.
   */
  public function __construct(
    RequestStack $request,
    LanguageManagerInterface $language_manager,
    UrlGeneratorInterface $url_generator,
    LoggerChannelFactory $logger_factory
  ) {
    $this->request = $request;
    $this->languageManager = $language_manager;
    $this->urlGenerator = $url_generator;
    $this->logger = $logger_factory->get('alshaya_social');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SocialAuthEvents::BEFORE_PROVIDER_REDIRECT][] = ['beforeProviderRedirect', 100];
    $events[SocialAuthEvents::PROVIDER_ERROR][] = ['onProviderError', 100];
    return $events;
  }

  /**
   * Set login_error_desitnation to redirect back.
   *
   * @param \Drupal\social_auth\Event\ProviderRedirectEvent $event
   *   The event object.
   */
  public function beforeProviderRedirect(ProviderRedirectEvent $event) {
    $base_url = $this->request->getCurrentRequest()->getSchemeAndHttpHost() . $this->request->getCurrentRequest()->getBasePath() . '/' . $this->languageManager->getCurrentLanguage()->getId();
    $error_destination = str_replace($base_url, '', $this->request->getCurrentRequest()->server->get('HTTP_REFERER'));
    $event->getDataHandler()->set('login_error_destination', $error_destination);
  }

  /**
   * Redirect to login_error_destination on provider error.
   *
   * @param \Drupal\social_auth\Event\ProviderErrorEvent $event
   *   The event object.
   */
  public function onProviderError(ProviderErrorEvent $event) {
    if ($error_dest = $event->getDataHandler()->get('login_error_destination')) {
      $event->getDataHandler()->set('login_error_destination', NULL);
      $redirectPath = Url::fromUserInput($error_dest);
      $url = $this->urlGenerator->generateFromRoute($redirectPath->getRouteName(), $redirectPath->getRouteParameters(), ['absolute' => TRUE]);
      $event->setResponse(new RedirectResponse($url, '302'));
    }
  }

}
