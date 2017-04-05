<?php

namespace Drupal\alshaya_acm\EventSubscriber;

use Drupal\alshaya_acm\AddToCartErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AddToCartErrorEventSubscriber.
 *
 * @package Drupal\alshaya_acm\EventSubscriber
 */
class AddToCartErrorEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AddToCartErrorEvent::SUBMIT][] = ['showerrormessage', 800];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_acm\AddToCartErrorEvent $event
   *   Exception raised.
   */
  public function showerrormessage(AddToCartErrorEvent $event) {

    $exception = $event->getEventException();

    // Logs a notice.
    \Drupal::logger('alshaya_acm')->notice($exception->getMessage());

    // Set an error here, which can be shown to the user.
    drupal_set_message(AddToCartErrorEvent::SUBMIT . ' | ' . $exception->getMessage(), 'error');
  }

}
