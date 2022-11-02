<?php

namespace Drupal\alshaya_xb\EventSubscriber;

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
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request) {
    $this->request = $request;
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

    // @todo For POC we have hardcoded the currency to OMR,
    // this needs to be changed later.
    $cookie_value = json_encode([
      'countryISO' => 'OM',
      'currencyCode' => 'OMR',
      'cultureCode' => 'ar',
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
    $events[KernelEvents::RESPONSE][] = ['setXbCookie', -10];
    return $events;
  }

}
