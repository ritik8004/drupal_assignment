<?php

namespace Drupal\alshaya_acm\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the kernel request event to check env specific config.
 */
class AlshayaAcmSubscriber implements EventSubscriberInterface {

  /**
   * Check environment specific config on each request.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function checkAcmConfig(GetResponseEvent $event) {
    \Drupal::service('alshaya_acm.config_check')->checkConfig();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkAcmConfig'];
    return $events;
  }

}
