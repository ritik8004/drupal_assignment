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
    $cookie_value = $this->request->getCurrentRequest()->cookies->get('GlobalE_Data');
    if (empty($cookie_value)) {
      $response = $event->getResponse();
      // @todo For POC we have hardcoded the currency to OMR,
      // this needs to be changed later.
      $response->headers->setCookie(new Cookie('GlobalE_Data', '{"countryISO":"OM","currencyCode":"OMR","cultureCode":"ar"}'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['setXbCookie', -10];
    return $events;
  }

}
