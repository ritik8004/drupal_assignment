<?php

namespace App\EventListener;

use App\Service\Config\SystemSettings;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class KernelEventsListener.
 *
 * @package App\EventListener
 */
class KernelEventsListener {

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $systemSettings;

  /**
   * KernelEventsListener constructor.
   *
   * @param \App\Service\Config\SystemSettings $system_settings
   *   System Settings service.
   */
  public function __construct(SystemSettings $system_settings) {
    $this->systemSettings = $system_settings;
  }

  /**
   * This method is executed on kernel.response event.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   Response event.
   */
  public function onKernelResponse(ResponseEvent $event) {
    if (!$event->isMasterRequest()) {
      return;
    }

    $response = $event->getResponse();

    // Disable caching for all the requests.
    $response->setMaxAge(0);
    $response->headers->set('cache-control', 'must-revalidate, no-cache, no-store, private');

    if ($event->getRequest()->isSecure()) {
      $settings = $this->systemSettings->getSettings('alshaya_security');
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
      $response->headers->set('Strict-Transport-Security', $header);
    }
  }

}
