<?php

namespace Drupal\alshaya_wishlist\EventSubscriber;

use Drupal\social_auth\Event\SocialAuthEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\social_auth\Event\UserEvent;

/**
 * Subscriber to set a social login identifier cookie.
 */
class AlshayaWishlistEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[SocialAuthEvents::USER_LOGIN][] = ['onUserLogin'];
    return $events;
  }

  /**
   * Set a cookie if user logged in via social provider.
   *
   * @param \Drupal\social_auth\Event\UserEvent $event
   *   The event object.
   */
  public function onUserLogin(UserEvent $event) {
    // Set an additional cookie to utilise once on FE to perform some action.
    // Example, we use this cookie to enable wishlist merge once on FE and
    // then remove this in second time. This is because, first time page loads
    // in the social callback popup where we need to avoid such actions.
    user_cookie_save(['alshaya_user_login_type' => 'social_login']);
  }

}
