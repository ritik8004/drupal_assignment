<?php

namespace Drupal\alshaya_security;

use Drupal\Core\Site\Settings;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the kernel respond event to handle security.
 */
class AlshayaSecuritySubscriber implements EventSubscriberInterface {

  /**
   * Update headers as per security recommendations.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   */
  public function onRespond(FilterResponseEvent $event) {
    $request = $event->getRequest();

    // Add HSTS if on secure page.
    if ($request->isSecure()) {
      $settings = Settings::get('alshaya_security');
      // Add the max age header.
      $header = 'max-age=' . $settings['max_age'];

      // Include subdomains if enabled.
      if ($settings['subdomains']) {
        $header .= '; includeSubDomains';
      }

      // Include preload if enabled.
      if ($settings['preload']) {
        $header .= '; preload';
      }

      // Add the header for HSTS.
      $event->getResponse()->headers->set('Strict-Transport-Security', $header);

      // Add no-store if no-cache is available.
      if ($cache_control = $event->getResponse()->headers->get('cache-control')) {
        if (strpos($cache_control, 'no-cache') > -1) {
          $cache_control .= ', no-store';
          $event->getResponse()->headers->set('cache-control', $cache_control);
        }
      }
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }

}
