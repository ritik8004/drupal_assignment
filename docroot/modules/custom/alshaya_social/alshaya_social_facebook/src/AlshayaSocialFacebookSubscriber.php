<?php

namespace Drupal\alshaya_social_facebook;

use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\SocialAuthUserFieldsEvent;
use Drupal\social_auth_facebook\FacebookAuthManager;
use Drupal\social_auth_facebook\FacebookAuthPersistentDataHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to fill first and last name for user using facebook.
 */
class AlshayaSocialFacebookSubscriber implements EventSubscriberInterface {

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  private $networkManager;

  /**
   * The Facebook authentication manager.
   *
   * @var \Drupal\social_auth_facebook\FacebookAuthManager
   */
  protected $facebookManager;

  /**
   * The Facebook persistent data handler.
   *
   * @var \Drupal\social_auth_facebook\FacebookAuthPersistentDataHandler
   */
  protected $persistentDataHandler;

  /**
   * AlshayaSocialFacebookSubscriber constructor.
   *
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of social_auth_facebook network plugin.
   * @param \Drupal\social_auth_facebook\FacebookAuthManager $facebook_manager
   *   Used to manage authentication methods.
   * @param \Drupal\social_auth_facebook\FacebookAuthPersistentDataHandler $persistent_data_handler
   *   Used for reading data from and writing data to session.
   */
  public function __construct(NetworkManager $network_manager, FacebookAuthManager $facebook_manager, FacebookAuthPersistentDataHandler $persistent_data_handler) {
    $this->networkManager = $network_manager;
    $this->facebookManager = $facebook_manager;
    $this->persistentDataHandler = $persistent_data_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SocialAuthEvents::USER_FIELDS][] = ['onUserFields', 100];
    return $events;
  }

  /**
   * Add first name and last name info from facebook profile.
   *
   * @param \Drupal\social_auth\Event\SocialAuthUserFieldsEvent $event
   *   The social auth user fields event object.
   */
  public function onUserFields(SocialAuthUserFieldsEvent $event) {
    $access_token = $this->persistentDataHandler->get('access_token');
    var_dump($access_token);
    /* @var \Facebook\Facebook|false $facebook */
    $facebook = $this->networkManager->createInstance('social_auth_facebook')->getSdk();
    $this->facebookManager->setClient($facebook)->authenticate();

    // Gets user's FB profile from Facebook API.
    if ($fb_profile = $this->facebookManager->getUserInfo('id,first_name,last_name,email')) {
      $fields = $event->getUserFields();
      $fields['field_first_name'] = $fb_profile->getField('first_name');
      $fields['field_last_name'] = $fb_profile->getField('last_name');
      if (!isset($fields['mail'])) {
        $fields['mail'] = $fb_profile->getField('email');
      }

      $event->setUserFields($fields);
    }
  }

}
