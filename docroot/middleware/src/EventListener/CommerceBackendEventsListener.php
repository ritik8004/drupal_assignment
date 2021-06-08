<?php

namespace App\EventListener;

use App\Service\Config\SystemSettings;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Listens to events for commerce backend.
 *
 * @package App\EventListener
 */
class CommerceBackendEventsListener {

  /**
   * System settings service.
   *
   * @var App\Service\Config\SystemSettings
   */
  protected $systemSettings;

  /**
   * The CommerceBackendEventsListener constructor.
   *
   * @param App\Service\Config\SystemSettings $system_settings
   *   System settings service.
   */
  public function __construct(SystemSettings $system_settings) {
    $this->systemSettings = $system_settings;
  }

  /**
   * This method is executed in kernel.request event.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Request event.
   */
  public function onKernelRequest(RequestEvent $event) {
    $version = $this->systemSettings->getSettings('commerce_backend')['version'];
    // If we set the backend as Magento and we are trying to access middleware,
    // then we do not allow that.
    if ($version === 'v2') {
      $response = new Response('Trying to acccess V1 when version is V2.', Response::HTTP_FORBIDDEN);
      $event->setResponse($response);
    }
  }

}
