<?php

namespace Drupal\acq_sku\EventSubscriber;

use Drupal\acq_sku\AddToCartErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AddToCartErrorEventSubscriber.
 *
 * @package Drupal\acq_sku\EventSubscriber
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
   * @param \Drupal\acq_sku\AddToCartErrorEvent $event
   *   Exception raised.
   */
  public function showerrormessage(AddToCartErrorEvent $event) {

    $exception = $event->getEventException();

    // Logs a notice.
    \Drupal::logger('acq_sku')->notice($exception->getMessage());

    // Set an error here, which can be shown to the user.
    drupal_set_message(AddToCartErrorEvent::SUBMIT . ' | ' . $exception->getMessage(), 'error');
  }

}
