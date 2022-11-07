<?php

namespace Drupal\alshaya_xb\EventSubscriber;

use Drupal\alshaya_xb\Service\DomainConfigOverrides;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
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
   * Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $logger;

  /**
   * SetXBCookieSubscriber constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The Request stack.
   * @param \Drupal\alshaya_xb\Service\DomainConfigOverrides $domain_config
   *   Domain config overrides.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger factory.
   */
  public function __construct(RequestStack $request,
                              DomainConfigOverrides $domain_config,
                              LoggerChannelFactoryInterface $logger) {
    $this->request = $request;
    $this->domainConfigOverrides = $domain_config;
    $this->logger = $logger;
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

    // Get config overrides by domain.
    $configOverrides = $this->domainConfigOverrides->getXbConfigByDomain();

    // Return if configOverrides is empty.
    if (empty($configOverrides)) {
      return;
    }

    $cookie_value = json_encode([
      'countryISO' => $configOverrides['code'] ?? NULL,
      'currencyCode' => $configOverrides['currency_code'] ?? NULL,
      'cultureCode' => $configOverrides['culture_code'] ?? NULL,
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
