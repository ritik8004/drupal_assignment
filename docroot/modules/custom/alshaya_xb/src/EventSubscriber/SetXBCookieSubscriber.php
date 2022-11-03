<?php

namespace Drupal\alshaya_xb\EventSubscriber;

use Drupal\alshaya_xb\Service\DomainConfigOverrides;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Domain currency cookie set subscriber class.
 */
class SetXBCookieSubscriber implements EventSubscriberInterface {

  /**
   * Request service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Domain config overrides for xb.
   *
   * @var \Drupal\alshaya_xb\Service\DomainConfigOverrides
   */
  protected DomainConfigOverrides $domainConfigOverrides;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request,
                              DomainConfigOverrides $domain_config) {
    $this->request = $request;
    $this->domainConfigOverrides = $domain_config;
  }

  /**
   * React to the symfony kernel response event by managing visitor cookies.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function setXbCookie(ResponseEvent $event) {
    if (!empty($this->request->getCurrentRequest()->cookies->get('GlobalE_Data'))) {
      return;
    }

    // Get configs by domain.
    $xbConfig = $this->domainConfigOverrides->getXbConfigByDomain();

    $cookie_value = json_encode([
      'countryISO' => $xbConfig['code'],
      'currencyCode' => $xbConfig['currency_code'],
      'cultureCode' => $xbConfig['culture_code'],
    ]);

    $cookie = new Cookie(
      'GlobalE_Data',
      $cookie_value,
      0,
      '/',
      NULL,
      NULL,
      FALSE,
      TRUE,
      NULL
    );

    $response = $event->getResponse();
    $response->headers->setCookie($cookie);
    $event->setResponse($response);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::RESPONSE][] = ['setXbCookie', -10];
    return $events;
  }

}
