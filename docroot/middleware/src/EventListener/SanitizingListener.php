<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Performs sanitizing activity on request.
 *
 * @package App\EventListener
 */
class SanitizingListener {

  /**
   * This method is executed on kernel.request event.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Request event.
   */
  public function onKernelRequest(RequestEvent $event) {
    if (!$event->isMasterRequest()) {
      return;
    }

    $request = $event->getRequest();

    // Check if langcode is something different from en/ar. If so, then set
    // en as the default.
    preg_match('/lang=(\w{2})/', $request->getQueryString(), $match);
    if ((isset($match[1])) && (!in_array($match[1], ['en', 'ar']))) {
      $request->query->set('lang', 'en');
    }
  }

}
