<?php

namespace App\EventListener;

use App\Service\Config\SystemSettings;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class Kernel Events Listener.
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
   * Logger Interface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * KernelEventsListener constructor.
   *
   * @param \App\Service\Config\SystemSettings $system_settings
   *   System Settings service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger Interface.
   */
  public function __construct(SystemSettings $system_settings,
                              LoggerInterface $logger) {
    $this->systemSettings = $system_settings;
    $this->logger = $logger;
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

  /**
   * This method is executed on kernel.request event.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Request event.
   */
  public function onKernelRequest(RequestEvent $event) {
    $request = $event->getRequest();
    // If API request not from web.
    if (!empty($request->headers->get($_ENV['MAGENTO_BEARER_HEADER']))) {
      $this->logger->notice('Non web middleware API request.');
    }
  }

}
