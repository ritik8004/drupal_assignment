<?php

namespace Drupal\alshaya_social_facebook\EventSubscriber;

use Drupal\alshaya_social\AlshayaSocialHelper;
use Drupal\social_auth\AuthManager\OAuth2ManagerInterface;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\UserFieldsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to fill first and last name for user using facebook.
 */
class AlshayaSocialFacebookSubscriber implements EventSubscriberInterface {

  /**
   * The Facebook authentication manager.
   *
   * @var \Drupal\social_auth\AuthManager\OAuth2ManagerInterface
   */
  protected $providerAuth;

  /**
   * The social auth helper.
   *
   * @var \Drupal\alshaya_social\AlshayaSocialHelper
   */
  protected $socialHelper;

  /**
   * AlshayaSocialFacebookSubscriber constructor.
   *
   * @param \Drupal\social_auth\AuthManager\OAuth2ManagerInterface $provider_auth
   *   The provider auth manager.
   * @param \Drupal\alshaya_social\AlshayaSocialHelper $social_helper
   *   The social auth helper.
   */
  public function __construct(
    OAuth2ManagerInterface $provider_auth,
    AlshayaSocialHelper $social_helper
  ) {

    $this->providerAuth = $provider_auth;
    $this->socialHelper = $social_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[SocialAuthEvents::USER_FIELDS][] = ['onUserFields'];
    return $events;
  }

  /**
   * Add first name and last name info from facebook profile.
   *
   * @param \Drupal\social_auth\Event\UserFieldsEvent $event
   *   The social auth user fields event object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function onUserFields(UserFieldsEvent $event) {
    if ($event->getPluginId() !== 'social_auth_facebook') {
      return;
    }

    if ($fields = $this->socialHelper->socialAuthUserFields($this->providerAuth, $event)) {
      $event->setUserFields($fields);
    }
  }

}
