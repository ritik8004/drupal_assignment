<?php

namespace Drupal\alshaya_spc\EventSubscriber;

use Drupal\alshaya_spc\Event\UserLoginEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Hooks into the user login event to perform authentication with Magento API.
 *
 * @package Drupal\alshaya_spc\EventSubscriber
 */
class UserLoginSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      UserLoginEvent::EVENT_NAME => 'onUserLogin',
    ];
  }

  /**
   * Subscribe to the user login event dispatched.
   *
   * @param \Drupal\alshaya_spc\Event\UserLoginEvent $event
   *   The event.
   */
  public function onUserLogin(UserLoginEvent $event) {
    die('user logged in');
  }

}
