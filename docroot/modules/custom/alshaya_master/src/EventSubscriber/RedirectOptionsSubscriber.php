<?php

namespace Drupal\alshaya_master\EventSubscriber;

use Drupal\r4032login\Event\RedirectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RedirectOptionsSubscriber
 *
 * Remove query parameters from redirect options.
 */
class RedirectOptionsSubscriber implements EventSubscriberInterface {

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * The array keys are event names and the value can be:
   *
   *  * The method name to call (priority defaults to 0)
   *  * An array composed of the method name to call and the priority
   *  * An array of arrays composed of the method names to call and respective
   *    priorities, or 0 if unset
   *
   * For instance:
   *
   *  * array('eventName' => 'methodName')
   *  * array('eventName' => array('methodName', $priority))
   *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
   *
   * @return array The event names to listen to
   */
  public static function getSubscribedEvents() {
    $events[RedirectEvent::EVENT_NAME] = ['onRedirect', 100];

    return $events;
  }

  /**
   * Alter redirect url before the url is perform.
   *
   * @param \Drupal\r4032login\Event\RedirectEvent
   *   The Event to process.
   */
  public function onRedirect(RedirectEvent $event) {
    $options = $event->getOptions();
    unset($options['query']);
    $event->setOptions($options);
  }

}
