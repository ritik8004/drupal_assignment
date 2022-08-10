<?php

namespace Drupal\alshaya_social\EventSubscriber;

use Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\social_auth\Event\BeforeRedirectEvent;
use Drupal\social_auth\Event\FailedAuthenticationEvent;
use Drupal\social_auth\Event\SocialAuthEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\social_auth\Event\UserEvent;

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
   * The customer helper.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper
   */
  protected $customerHelper;

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
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcCustomerHelper $customer_helper
   *   Customer helper object.
   */
  public function __construct(
    RequestStack $request,
    LanguageManagerInterface $language_manager,
    UrlGeneratorInterface $url_generator,
    LoggerChannelFactory $logger_factory,
    AlshayaSpcCustomerHelper $customer_helper
  ) {
    $this->request = $request;
    $this->languageManager = $language_manager;
    $this->urlGenerator = $url_generator;
    $this->logger = $logger_factory->get('alshaya_social');
    $this->customerHelper = $customer_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[SocialAuthEvents::BEFORE_REDIRECT][] = [
      'beforeProviderRedirect',
      100,
    ];
    $events[SocialAuthEvents::FAILED_AUTH][] = ['onFailedAuth', 100];
    $events[SocialAuthEvents::USER_LOGIN][] = ['onUserLogin', 100];
    return $events;
  }

  /**
   * Set login_error_desitnation to redirect back.
   *
   * @param \Drupal\social_auth\Event\BeforeRedirectEvent $event
   *   The event object.
   */
  public function beforeProviderRedirect(BeforeRedirectEvent $event) {
    $base_url = $this->request->getCurrentRequest()->getSchemeAndHttpHost() . $this->request->getCurrentRequest()->getBasePath() . '/' . $this->languageManager->getCurrentLanguage()->getId();
    $error_destination = str_replace($base_url, '', $this->request->getCurrentRequest()->server->get('HTTP_REFERER'));
    $event->getDataHandler()->set('login_error_destination', $error_destination);
  }

  /**
   * Redirect to login_error_destination on provider error.
   *
   * @param \Drupal\social_auth\Event\FailedAuthenticationEvent $event
   *   The event object.
   */
  public function onFailedAuth(FailedAuthenticationEvent $event) {
    if ($error_dest = $event->getDataHandler()->get('login_error_destination')) {
      $event->getDataHandler()->set('login_error_destination', NULL);
      $redirectPath = Url::fromUserInput($error_dest);
      $url = $this->urlGenerator->generateFromRoute($redirectPath->getRouteName(), $redirectPath->getRouteParameters(), ['absolute' => TRUE]);
      $event->setResponse(new RedirectResponse($url, '302'));
    }
  }

  /**
   * Fetch and set user customer token in session.
   *
   * @param \Drupal\social_auth\Event\UserEvent $event
   *   The event object.
   */
  public function onUserLogin(UserEvent $event) {
    $mail = $event->getUser()->getEmail();
    $this->customerHelper->loadCustomerTokenForSocialAccount($mail);
  }

}
