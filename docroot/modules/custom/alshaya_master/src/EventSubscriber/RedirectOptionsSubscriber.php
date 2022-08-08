<?php

namespace Drupal\alshaya_master\EventSubscriber;

use Drupal\r4032login\Event\RedirectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RedirectOptionsSubscriber.
 *
 * Remove query parameters from redirect options.
 */
class RedirectOptionsSubscriber implements EventSubscriberInterface {

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * @return array
   *   The event names to listen to
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[RedirectEvent::EVENT_NAME] = ['onRedirect', 100];

    return $events;
  }

  /**
   * Alter redirect url before the url is perform.
   *
   * @param \Drupal\r4032login\Event\RedirectEvent $event
   *   The Event to process.
   */
  public function onRedirect(RedirectEvent $event) {
    $options = $event->getOptions();
    unset($options['query']);
    $event->setOptions($options);
  }

}
